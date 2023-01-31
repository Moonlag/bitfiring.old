<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Str;
use Cookie;
class WheelRewardController extends Controller
{
    public function check_phase(Request $request){
        $input = $request->all();

        $validator = Validator::make($input, [
            'user_id' => 'required',
        ]);

        if ($validator->passes()) {

            $player = \App\Models\Players::find($input['user_id']);

            $deposit = $this->deposits($player);

            if($this->bonus_issue($player, 7) && $deposit <= 500){
                return response()->json(['phase' => 7]);
            }

            if($this->bonus_issue($player, 7) && $deposit >= 10000){
                return response()->json(['phase' => 6]);
            }

            if($this->bonus_issue($player, 7) && ($deposit >= 2000 && $deposit <= 9999)){
                return response()->json(['phase' => 5]);
            }

            if($this->bonus_issue($player, 7) && ($deposit >= 500 && $deposit <= 1999)){
                return response()->json(['phase' => 4]);
            }

            if(!$this->bonus_issue($player, 7) && $deposit > 0){
                return response()->json(['phase' => 3]);
            }

            return response()->json(['phase' => 2]);

        }

        return response()->json(['errors' => $validator->errors()->all(), 'error_keys' => array_keys($validator->errors()->messages())]);
    }

    public function bonus_issue($player, $bonus_id)
    {
        return \App\Models\BonusIssue::query()
            ->where('user_id', $player->id)
            ->where('bonus_id', $bonus_id)
            ->count();
    }

    public function deposits($player)
    {
        $payments = \App\Models\Payments::query()
            ->where('user_id', $player->id)
            ->where('payments.type_id', 3)
            ->where('payments.status', 1)
            ->join('currency', 'payments.currency_id', '=', 'currency.id')
            ->select('payments.amount', 'currency.rate')
            ->get();

        $deposit = $payments->map(function ($item, $key) {
            return ((float)$item->amount / (float)$item->rate);
        })->sum();;

        return $deposit;
    }

    public function set_rewards(Request $request)
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'spin_id' => 'required',
        ]);


        if ($validator->passes()) {

            $response = Http::get("https://bitfiring.back-dev.com/lucky/public/api/spins/" . $input['spin_id']);
            $input = $response->json('data');

            $validator = Validator::make($input, [
                'reward_id' => 'required',
                'user_id' => 'required',
                'issued' => 'required',
            ]);

            if($input['issued'] == 1){
                $validator->errors()->add('issued', 'Spin Issued');
            }

            $data = [];

            if ($validator->passes()) {
                switch ($input['reward_id']) {
                    case '1':
                       $data = $this->get_freespin($input['user_id'], 1);
                        break;
                    case '2':
                        $data = $this->get_bonus_issue($input['user_id'], 19);
                        break;
                    case '3':
                        $data = $this->get_bonus_issue($input['user_id'], 20);
                        break;
                    case '4':
                        $data = $this->get_freespin($input['user_id'], 4);
                        break;
                    case '5':
                        $data = $this->get_freespin($input['user_id'], 5);
                        break;
                    case '6':
                        $data = $this->get_freespin($input['user_id'], 6);
                        break;
                    case '7':
                        $data = $this->get_bonus_issue($input['user_id'], 21);
                        break;
                    case '8':
                        $data = $this->get_bonus_issue($input['user_id'], 22);
                        break;
                    case '9':
                        $data =$this->get_bonus($input['user_id'], 14, 50, '50 USDT bonus');
                        break;
                    case '10':
                        $data = $this->get_bonus($input['user_id'], 14, 500, '500 USDT bonus');
                        break;
                    case '11':
                        $data = $this->get_bonus($input['user_id'], 14, 5000, '5000 USDT bonus');
                        break;
                }

                event(new \App\Events\UpdateNotification($input['user_id']));

                return response()->json($data);
            }
        }

        return response()->json(['errors' => $validator->errors()->all(), 'error_keys' => array_keys($validator->errors()->messages())]);
    }

    public function get_bonus($user_id, $currency_id, $amount, $title, $wager = 70)
    {
        $currency = DB::table('currency')->where('id', $currency_id)->first();
        $amount = (float)$amount;
        $valid_until = \Illuminate\Support\Carbon::now()->addDays(3);
        $bonus_amount = $amount / $currency->rate;

        DB::table('bonuses_user')->insert([
            'user_id' => $amount,
            'published' => 1,
            'stage' => 2,
            'currency' => $currency_id,
            'bonus_id' => 0,
            'amount' => $bonus_amount,
            'wager' => $wager,
            'created_at' => \Illuminate\Support\Carbon::now(),
        ]);

        $locked_amount = $bonus_amount;
        $to_wager = (float)$wager * $bonus_amount;

        $id = DB::table('bonus_issue')->insertGetId([
            'user_id' => $user_id,
            'currency_id' => $currency_id,
            'bonus_id' => 0,
            'amount' => $bonus_amount,
            'locked_amount' => $locked_amount,
            'fixed_amount' => $locked_amount,
            'active_until' => $valid_until,
            'to_wager' => $to_wager,
            'wagered' => 0,
            'stage' => 2,
            'status' => 1,
            'admin_id' => 1,
            'custom_title' => $title,
            'created_at' => \Illuminate\Support\Carbon::now(),
        ]);

        return ['id' => $id];
    }

    public function get_freespin($user_id, $freespin_id)
    {
        $freespin = \App\Models\FreespinBonusModel::query()
            ->where('id', '=', $freespin_id)
            ->first();

        $expiry = new \App\Models\Bonuses();
        $expiry_method = $freespin->activate_duration_type ? $expiry::DURATION[$freespin->activate_duration_type] : null;

        \App\Models\FreespinIssue::query()
            ->where('player_id', '=', $user_id)
            ->where('status', '=', 1)
            ->update([
                'status' => 2
            ]);

       return \App\Models\FreespinIssue::query()
            ->create([
                'title' => $freespin->title,
                'player_id' => $user_id,
                'currency_id' => $freespin->currency_id,
                'bonus_id' => $freespin->id,
                'count' => $freespin->count,
                'win' => 0,
                'stage' => 2,
                'status' => 1,
                'active_until' => $expiry_method ? $expiry->$expiry_method($freespin->activate_duration) : null
            ]);
    }

    public function get_bonus_issue($user_id, $bonus_id)
    {
       DB::table('bonus_options')
           ->where('player_id', $user_id)
           ->where('enabled', 1)
           ->update([
               'enabled' => 0
           ]);

        $id = DB::table('bonus_options')->insertGetId([
            'player_id' => $user_id,
            'bonus_id' => $bonus_id,
            'enabled' => 1
        ]);

        return ['id' => $id];
    }

    public function auth_player(Request $request){
        $input = $request->all();
        $this->common_data = \Request::get('common_data');

        $user = $this->common_data['user'];
        if (!empty($request->cookie('lucky'))) {
            $input['session_id'] = $request->cookie('lucky');
        }

        if(isset($user->id)){
           $response = Http::post("https://bitfiring.back-dev.com/lucky/public/auth", [
                'user_id' => $user->id
            ]);
        } else if(isset($input['session_id'])){
            $response = Http::post("https://bitfiring.back-dev.com/lucky/public/auth", [
                'session_id' => $input['session_id']
            ]);
        } else {
            $response = Http::post("https://bitfiring.back-dev.com/lucky/public/auth");
        }

        $response = $response->json();

        if($response){
            $now = Carbon::now();
            $dtRe = Carbon::parse($response['re_spin']);
            $life_time = $now->diffInMinutes($dtRe);
            Cookie::queue('lucky', $response['session'], $life_time);
        }

        return response()->json($response);
    }

    public function spin_player(Request $request){
        $input = $request->all();
        $this->common_data = \Request::get('common_data');

        if (empty($request->cookie('lucky'))) {
            return response()->json(['errors' => ['session' => 'Session not Found']]);
        }

        $response = Http::post("https://bitfiring.back-dev.com/lucky/public/spin", ['session_id' => $request->cookie('lucky'), 'project_id' => 1, 'ip' => $request->ip()]);

        $response = $response->json();

        return response()->json($response);
    }
}
