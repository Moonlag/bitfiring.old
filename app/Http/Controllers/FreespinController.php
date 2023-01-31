<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Traits\BgaminFreespinTrait;
use App\Http\Traits\ServiceTrait;
use App\Models\FreespinIssue;
use Str;
use Carbon\Carbon;
use DB;
class FreespinController extends Controller
{
    use BgaminFreespinTrait, ServiceTrait;

    public function get_freespin(Request $request){
        $this->common_data = \Request::get('common_data');
        $input = $request->all();

        $freespin_issue_id = $input['id'];

        if (isset($this->common_data['user']->id)) {

            $freespinIssue = FreespinIssue::query()
                ->where('id', $freespin_issue_id)
                ->where('player_id', $this->common_data['user']->id)
                ->where('status', 1)
                ->where('stage', 2)
                ->first();

            if(!isset($freespinIssue->id)){
                $freespinIssue = FreespinIssue::query()
                    ->where('id', $freespin_issue_id)
                    ->where('player_id', $this->common_data['user']->id)
                    ->first();

                if(!isset($freespinIssue->id)) {
                    return response()->json(['error' => 'Freespin not Found'], 404);
                }

                $freespinBonus = $freespinIssue->freespin_bonus;
                $freespinGames = $freespinBonus->bonus_games->first();

                return response()->json($freespinGames);
            }

            $freespinBonus = $freespinIssue->freespin_bonus;
            $freespinGames = $freespinBonus->bonus_games->first();

            $userWallet = $this->common_data['user']->wallets->where('currency_id', $freespinBonus->currency_id)->first();

            if(!isset($userWallet->id)){
                return response()->json(['error' => 'Wallet not Found'], 404);
            }

            if(!isset($freespinGames->id)){
                return response()->json(['error' => 'Game not Found'], 404);
            }

            $stage_code = Str::random(25);
            $freespinIssue->issue_code = $stage_code;
            $freespinIssue->wallet_id = $userWallet->id;
            $freespinIssue->stage = 1;
            $freespinIssue->status = 1;
            $freespinIssue->save();

            event(new \App\Events\UpdateNotification($this->common_data['user']->id));

            switch ($freespinGames->provider){
                case "bgaming":
                    $this->prepare_bgaming('Pfhv5mqW8PvmsPWc4uaLg7gp');

                    $denomination = $this->get_denomination($freespinBonus->currency_id, $freespinBonus->provider_id);
                    $result = $this->bgaming_freespinsIssue([
                        "casino_id" => "bitfiring",
                        "issue_id" => $stage_code,
                        "currency" => $denomination->altercode,
                        "games" => [$freespinGames->identer],
                        "valid_until" => Carbon::tomorrow()->format('Y-m-d\TH:i:s\Z'),
                        "bet_level" => 1,
                        "freespins_quantity" => $freespinBonus->count,
                        "user" => [
                            "id" => $denomination->altercode.$this->common_data['user']->id,
                            "firstname" => $this->common_data['user']->firstname,
                            "lastname" => $this->common_data['user']->lastname,
                            "nickname" => "user".$this->common_data['user']->id,
                            "city" => $this->common_data['user']->city,
                            "date_of_birth" => date('Y-m-d'),
                            "registered_at" => date('Y-m-d'),
                            "gender" => "m",
                            "country" => "CA",
                        ],
                    ])->getBody()->getContents();

                    $result = json_decode($result);

                    return response()->json($freespinGames);
            }



            return response()->json('Auth Error', 404);
        }

    }

    public function cancel_freespin(Request $request){
        $this->common_data = \Request::get('common_data');
        $input = $request->all();

        $freespin_issue_id = $input['id'];

        if (isset($this->common_data['user']->id)) {
            $freespinIssue = FreespinIssue::query()
                ->where('id', $freespin_issue_id)
                ->where('player_id', $this->common_data['user']->id)
                ->where('status', 2)
                ->where('stage', 2)
                ->first();

            if(!isset($freespinIssue->id)){
                return response()->json(['error' => 'Freespin not Found'], 404);
            }

            $freespinBonus = $freespinIssue->freespin_bonus;
            $freespinGames = $freespinBonus->bonus_games->first();

            switch ($freespinGames->provider) {
                case "bgaming":
                    $result = $this->bgaming_freespinsCancel([
                        "casino_id" => "bitfiring",
                        "issue_id" => $freespinIssue->issue_id,
                    ])->getBody()->getContents();

                    break;

            }

            $freespinIssue->stage = 3;
            $freespinIssue->status = 3;
            $freespinIssue->save();
            return response()->json(['success' => 1, ['freespin' => $freespinIssue]]);
        }

    }

    public function status_freespin(){

    }

    public function index(){
        $this->freespins_issue(1095, 14, 6.40, 1, 1);

        return response()->json([]);
    }

    public function freespins_issue($player_id, $currency_id, $amount, $freespin_id, $tx_id, $issue_id)
    {

        $freespinIssue = \App\Models\FreespinIssue::find($issue_id);
        $freespinIssue->status = 2;
        $freespinIssue->save();

        $freespin = \App\Models\FreespinBonusModel::find($freespin_id);
        $expiry = new \App\Models\Bonuses();
        $expiry_method = $freespin->duration_type ? $expiry::DURATION[$freespin->duration_type] : null;

        $wager_multiplier = $freespin->wager;

        $currency = \App\Models\Currency::find($currency_id);
        $amount = (float)$amount;

        $bonus_amount = $amount / $currency->rate;

        $locked_amount = $bonus_amount;
        $to_wager = (float)$wager_multiplier * $bonus_amount;

        DB::table('bonus_issue')->insert([
            'user_id' => $player_id,
            'currency_id' => $freespin->currency_id,
            'bonus_id' => $freespin_id,
            'amount' => $bonus_amount,
            'locked_amount' => $locked_amount,
            'fixed_amount' => $locked_amount,
            'to_wager' => $to_wager,
            'wagered' => 0,
            'stage' => 1,
            'status' => 2,
            'cat_type' => 2,
            'reference_id' => $tx_id,
            'custom_title' => $freespin->title,
            'created_at' => \Illuminate\Support\Carbon::now(),
            'active_until' => $expiry_method ? $expiry->$expiry_method($freespin->duration) : null,
        ]);
    }
}
