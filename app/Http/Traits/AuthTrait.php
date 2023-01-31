<?php

namespace App\Http\Traits;

use App\Models\Players;
use App\Models\VerifyPlayer;
use DB;
use Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Session;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Illuminate\Database\Query\Expression;

trait AuthTrait
{

    public function update_password($user_id, $password)
    {

        $result = DB::table('players')
            ->where('id', '=', $user_id)
            ->update(
                ['password' => Hash::make($password)]
            );

        return $result;

    }

    public function insert_session(array $array)
    {

        $array['created_at'] = Carbon::now()->toDateTimeString();
        DB::table('sessions')->insert($array);
        return 1;

    }

    public function do_registration(array $input)
    {

        $input['email_accept'] = isset($input['email_accept']) && $input['email_accept'] == "on" ? 1 : 0;
        $input['sms_accept'] = isset($input['sms_accept']) && $input['sms_accept'] == "on" ? 1 : 0;

        DB::table('players')->insert([
            'created_at' => date('Y-m-d H:i:s'),
            'useragent' => $input['useragent'],
            'firstname' => $input['firstname'],
            'lastname' => $input['lastname'],
            'fullname' => $input['firstname'] . ' ' . $input['lastname'],
            'dob' => $input['birthdate'],
            'city' => $input['city'],
            'address' => $input['address'],
            'postal_code' => $input['postalcode'],
            'promo_email' => $input['email_accept'],
            'promo_sms' => $input['sms_accept'],
            'email' => $input['email'],
            'password' => Hash::make($input['password']),
            'currency' => $input['currency'],
            'country' => $input['country'],
            'status' => 1,
        ]);

        return DB::getPdo()->lastInsertId();

    }

    public function create_player(array $input)
    {
        $player = Players::query()->create([
            'created_at' => date('Y-m-d H:i:s'),
            'email' => $input['email'],
            'city' => $input['city'] ?? null,
            'country_id' => $input['country_id'] ?? null,
            'useragent' => $input['useragent'],
            'password' => Hash::make($input['password']),
            'status' => 1,
            'affiliate_aid' => $input['affiliate_aid'] ?? null,
            'email_verified_at' => Carbon::now(),
            'register_ip' => $input['register_ip'],
            'mail_real' => $input['mail_real'] ?? 0,
        ]);
        $player->firstname = 'Player';
        $player->fullname = $player->firstname;
        $player->save();
        return $player;
    }

    public function verify_player($id){
        VerifyPlayer::query()->create([
            'token' => Str::random(60),
            'player_id' => $id
        ]);

    }


    public function check_withdraw_balance($user_id, $wallet_id, $amount)
    {

        $result = DB::table('wallets')->where([
            ['user_id', '=', $user_id],
            ['id', '=', $wallet_id],
        ])->first();

        if(isset($result->id)){
            return ($result->balance - $result->locked_balance) >= (float)$amount;
        }

        return 0;
    }

    public function check_locked_balance($user_id, $params, $amount)
    {

        $wallet = DB::table('wallets')->where([
            ['user_id', '=', $user_id],
        ])
            ->where($params)
            ->first();

        $currency_info = $this->get_single_currency('id', $wallet->currency_id);
        $converted_wallet = $wallet->balance / $currency_info->rate;
        $converted_amount = $amount / $currency_info->rate;

        $locked = DB::table('bonus_issue')
            ->select(DB::raw('SUM(fixed_amount) as locked_balance'))
            ->where([
                ['user_id', '=', $user_id],
                ['status', '=', 2]
            ])
            ->first();

        if (isset($wallet->id)) {
            return ((float)$converted_wallet - (float)$locked->locked_balance) >= (float)$converted_amount;
        }

        return 0;
    }

    public function check_swap_balance($user_id, $currency_id, $amount)
    {

        $result = DB::table('wallets')->where([
            ['user_id', '=', $user_id],
            ['currency_id', '=', $currency_id],
        ])->first();

        if(isset($result->id)){
            return $result->balance >= (float)$amount;
        }

        return 0;
    }

    public function transactions_swap($user_id, $currency_id, $amount, $type, $wallet_id, $swap_id)
    {
        DB::table('transactions')->insert([
            'player_id' => $user_id,
            'currency_id' => $currency_id,
            'amount' => $amount,
            'reference_id' => $swap_id,
            'reference_type_id' => 9,
            'type_id' => $type,
            'wallet_id' => $wallet_id
        ]);
    }

    public function swaps($from_id, $to_id, $from_amount, $to_amount)
    {
        DB::table('swaps')->insert([
            'from_id' => $from_id,
            'to_id' => $to_id,
            'from_amount' => $from_amount,
            'to_amount' => $to_amount,
        ]);

        return DB::getPdo()->lastInsertId();
    }

    public function wallet_swap($user_id, $currency_id){
        return DB::table('wallets')->where([
            ['user_id', '=', $user_id],
            ['currency_id', '=', $currency_id],
        ])->first();
    }

    public function update_wallet($args,$id){
        DB::table('wallets')->where('id', '=', $id)->update($args);
    }

    public function check_withdraw_cancel($user_id, $transaction_id){
        return DB::table('payments')->where([['user_id', '=', $user_id], ['id', '=', $transaction_id], ['status', '=', 2], ['type_id', '=', 4]])->count();
    }

    public function login($login, $password)
    {
        if (Auth::guard('clients')->attempt(['email' => $login, 'password' => $password])) {
            return 1;
        }

        return 0;

    }

    public function do_logout($request)
    {

        Auth::guard('clients')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return 1;

    }

    public function provide_headers()
    {

        $igc_token = Session::get('igc_token');
        $headers = ["AuthenticationToken" => $igc_token];

        return $headers;

    }

    public function get_ps()
    {

        return DB::table('payment_system')
            ->select()
            ->get();

        return $result;

    }

    public function get_bonuses($user_id)
    {

        return DB::table('bonuses_user')
            ->select()
            ->join('bonuses', 'bonuses.id', '=', 'bonuses_user.bonus_id')
            ->where('user_id', '=', $user_id)
            ->get();

        return $result;

    }

    public function get_sessions($user_id, $limit = 20)
    {

        return DB::table('sessions')
            ->select()
            ->where('user_id', '=', $user_id)
            ->orderByDesc('id')
            ->limit($limit)
            ->get();

        return $result;

    }

    //////////////////////////////

    public function is_logged_in()
    {

        $response = $this->client->post('Authentication/IsLoggedIn')->getBody()->getContents();

        $response = json_decode($response);
        $this->insert_watchdog_answer_log(['route' => __FUNCTION__, 'answer' => $response]);

        return $response->Data;

    }

    public function get_reg_types()
    {

        $response = $this->client->post('user/verify/registration/types')->getBody()->getContents();

        $response = json_decode($response);
        $this->insert_watchdog_answer_log(['route' => __FUNCTION__, 'answer' => $response]);

        return $response;

    }

    public function get_igc_token()
    {

        return Session::get('igc_token');

    }

    public function get_user()
    {

        $response = $this->client->post('User')->getBody()->getContents();

        $response = json_decode($response);
        $this->insert_watchdog_answer_log(['route' => __FUNCTION__, 'answer' => $response]);

        if ($_SERVER['REMOTE_ADDR'] == '95.67.24.220') {

            //pre($response);

        }

        return $response->Data;

    }

    public function verify_email($code)
    {

        $response = $this->client->post('Authentication/Verify/Email/' . $code)->getBody()->getContents();

        $response = json_decode($response);
        $this->insert_watchdog_answer_log(['route' => __FUNCTION__, 'answer' => $response]);

        return $response;

    }

    public function change_forgotten_password($input)
    {

        $response = $this->client->post('Authentication/ForgotPassword/Change', ['json' => $input])->getBody()->getContents();

        $response = json_decode($response);
        $this->insert_watchdog_answer_log(['route' => __FUNCTION__, 'answer' => $response]);

        return $response;

    }

    public function send_forgotten_request($input)
    {

        $response = $this->client->post('Authentication/ForgotPassword', ['json' => $input])->getBody()->getContents();

        $response = json_decode($response);
        $this->insert_watchdog_answer_log(['route' => __FUNCTION__, 'answer' => $response]);

        return $response;

    }


    public function do_user_update($input)
    {

        $response = $this->client->post('user/update', ['json' => $input])->getBody()->getContents();

        $response = json_decode($response);
        $this->insert_watchdog_answer_log(['route' => __FUNCTION__, 'answer' => $response]);

        return $response;

    }

    public function change_user_password($input)
    {

        $response = $this->client->post('Authentication/Change/Password', ['json' => $input])->getBody()->getContents();

        $response = json_decode($response);
        $this->insert_watchdog_answer_log(['route' => __FUNCTION__, 'answer' => $response]);

        return $response;

    }

    public function change_user_email($input)
    {

        $response = $this->client->post('Authentication/Change/Email', ['json' => $input])->getBody()->getContents();

        $response = json_decode($response);
        $this->insert_watchdog_answer_log(['route' => __FUNCTION__, 'answer' => $response]);

        return $response;

    }

    public function authenticate_by_token($challenge_token)
    {

        $response = $this->client->post('v2/authentication/authenticatewithchallenge?challengeToken=' . $challenge_token)->getBody()->getContents();

        $response = json_decode($response);
        $this->insert_watchdog_answer_log(['route' => __FUNCTION__, 'answer' => $response]);

        return $response;

    }

    public function get_user_authentication_token($input)
    {

        $response = $this->client->post('v2/authentication/Verify/ActivationToken/Get', ['json' => $input])->getBody()->getContents();

        $response = json_decode($response);
        $this->insert_watchdog_answer_log(['route' => __FUNCTION__, 'answer' => $response]);

        return $response;

    }

    public function send_verification_sms($input)
    {

        $response = $this->client->post('v2/authentication/verify/sms/send', ['json' => $input])->getBody()->getContents();

        $response = json_decode($response);
        $this->insert_watchdog_answer_log(['route' => __FUNCTION__, 'answer' => $response]);

        return $response;

    }

    public function verify_by_sms($input)
    {

        $response = $this->client->post('v2/authentication/verify/sms', ['json' => $input])->getBody()->getContents();

        $response = json_decode($response);
        $this->insert_watchdog_answer_log(['route' => __FUNCTION__, 'answer' => $response]);

        return $response;

    }


    public function register_external_user()
    {

        $response = $this->client->post('Games/RegisterExternalUser')->getBody()->getContents();

        $response = json_decode($response);
        $this->insert_watchdog_answer_log(['route' => __FUNCTION__, 'answer' => $response]);

        return $response;

    }

    public function get_session_details()
    {

        $response = $this->client->post('User/GetLoginDetails')->getBody()->getContents();

        $response = json_decode($response);
        $this->insert_watchdog_answer_log(['route' => __FUNCTION__, 'answer' => $response]);

        return $response;


    }

    public function validate_email($input, $ignore)
    {

        $response = $this->client->post('validate/email?ignoreExisting=' . $ignore, ['json' => $input])->getBody()->getContents();

        $response = json_decode($response);
        $this->insert_watchdog_answer_log(['route' => __FUNCTION__, 'answer' => $response]);

        return $response;

    }

    public function validate_username($input, $ignore)
    {

        $response = $this->client->post('validate/username?ignoreExisting=' . $ignore, ['json' => $input])->getBody()->getContents();

        $response = json_decode($response);
        $this->insert_watchdog_answer_log(['route' => __FUNCTION__, 'answer' => $response]);

        return $response;

    }


}
