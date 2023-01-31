<?php

namespace App\Http\Controllers;

use App\Events\NewDeposit;
use App\Events\UpdateBalance;
use App\Events\UpdateWallets;
use App\Http\Traits\AffiliateTrait;
use App\Http\Traits\BonusExpirationTrait;
use App\Http\Traits\EventTrait;
use App\Http\Traits\SwapTrait;
use App\Models\BonusIssue;
use App\Models\FreespinBonusModel;
use App\Models\FreespinIssue;
use App\Models\Game;
use App\Models\Partners;
use App\Models\Payments;
use App\Models\PaymentSystem;
use App\Models\Players;
use App\Models\VerifyPlayer;
use App\Models\Wallets;
use App\Rules\RealEmail;
use App\User;
use Carbon\Carbon;
use ErrorException;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Expression;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Response;
use LaravelLocalization;
use Illuminate\Http\Request;
use App\Http\Traits\DBTrait;
use App\Http\Traits\ServiceTrait;
use App\Http\Traits\LanguageTrait;
use App\Http\Traits\AuthTrait;
use App\Http\Traits\RoutineTrait;
use App\Http\Traits\PaymentTrait;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Http\RedirectResponse;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Validator;
use Jenssegers\Agent\Agent;
use Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Traits\EmailTrait;
use Illuminate\Support\Facades\Cookie;

class UserController extends Controller
{
    /*
    use ServiceTrait;
    use LanguageTrait;
    use RoutineTrait;
    use PaymentTrait;
    */

    use AuthTrait;
    use DBTrait;
    use AffiliateTrait;
    use BonusExpirationTrait;
    use EventTrait;
    use EmailTrait;
    use SwapTrait;

    public function ajax_auth_confirm(Request $request)
    {

        $response = ['errors' => []];
        $this->common_data = \Request::get('common_data');
        $input = $request->all();

        $output = $this->login($input['email'], $input['password']);

        if ($output) {
            try {
                $info = get_ip_info($request->ip());
                $input['city'] = $info['city'];
                $input['country'] = $info['countryCode'];
            } catch (ErrorException $exception) {
                $input['city'] = '';
                $input['country'] = '';
            }


            $user = Auth::guard('clients')->user();
            \Session::getHandler()->destroy($user->session_id);
            if ($user->groups->where('id', '=', 16)->count()) {
                $this->do_logout($request);
                return response()->json(['banned' => 1]);
            }


            $agent = new Agent();
            $view_type = $agent->isDesktop() ? 1 : 2;

            $user->session_id = session()->getID();

            $this->insert_session([
                "user_id" => $user->id,
                "user_agent" => $request->server('HTTP_USER_AGENT'),
                "device_type" => $view_type,
				"country" => $input['country'],
				"city" => $input['city'],
                "ip" => \Request::ip(),
                "device" => $agent->device(),
                "platform" => $agent->platform(),
                "browser" => $agent->browser(),
                "viewport" => $input['viewport']['w'] . 'x' . $input['viewport']['h']
            ]);

            $user->save();

            if (Auth::guard('clients')->check()) {
                $this->user_signed_in($user);

                if ($user->sessions()->where('ip', '=', \Request::ip())->count()) {
                    $timezone = \Illuminate\Support\Carbon::now();
                    $this->warn_attempt_email($user->email, $timezone, $agent->device() . ', ' . $agent->browser());
                };
            }

            $wallets = $this->get_user_wallets($user->id);
            $bonuses = $this->get_user_issued_bonus($user->id, [["bonus_issue.status", "=", 2]]);
            return response()->json(['success' => 1, 'user' => ['user' => $user, 'wallets' => $wallets, 'bonuses' => $bonuses]]);

        } else {
            $response['errors'][] = "Email and/or password are incorrect";
            $response['total_error'] = 1;
        }

        return Response::json($response, 404);
    }

    public function join_first(Request $request)
    {

        /*
        $this->common_data = \Request::get('common_data');
        $whois = $this->common_data['whois'];
        if($whois->IsExcluded) return redirect('/');
        */
        /*
        $language = $this->get_language_by_id($whois->UserLanguageId);

        if($language->Alpha2Code) {
            $error_translations = $this->get_translations_by_path("Global/Errors", $language->Alpha2Code);
            $common_translations = $this->get_translations_by_path("Common/System", $language->Alpha2Code);
        }

        $messages = [
            'required'       => $error_translations['Error_FIELD_REQUIRED']->Translation,
            'between' 		 => $error_translations['Error_FIELD_BETWEEN']->Translation,
            'username.regex' => $error_translations['Error_FIELD_USERNAME_FORMAT']->Translation,
            'password.regex' => $error_translations['Error_FIELD_PASSWORD_FORMAT']->Translation,
            'email' 		 => $error_translations['Error_FIELD_EMAIL']->Translation,
            'same' 			 => $error_translations['Error_FIELD_SAME']->Translation,
            'alpha' 		 => $error_translations['Error_FIELD_ALPHA']->Translation,
            'integer' 		 => $error_translations['Error_FIELD_INTEGER']->Translation,
            'string' 		 => $error_translations['Error_FIELD_STRING']->Translation,
        ];
        */

        $input = $request->all();

        $validator = Validator::make($input, [
            'password' => 'required|between:6,25|regex:/^.*(?=.{3,})(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9]).*$/',
            'password_confirm' => 'same:password',
            'email' => 'required|email',
            'countryid' => 'integer',
            'currencyid' => 'integer',
            'country' => 'required',
            'currency' => 'required',
        ]);

        if ($validator->passes()) {

            return response()->json(['success' => 1]);

        }

        return response()->json(['errors' => $validator->errors()->all(), 'error_keys' => array_keys($validator->errors()->messages())]);

    }

    public function join_confirm(Request $request)
    {

        $input = $request->all();

        $messages = [
            'day.required' => "The day of your birth is required",
            'month.required' => "The month of your birth is required",
            'year.required' => "The year of your birth is required",
        ];

        $field_rule = [
            'password' => 'required|between:8,25|regex:/^.*(?=.{3,})(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9]).*$/',
            'password_confirm' => 'same:password',
            'email' => 'required|email',
            'firstname' => 'required|alpha',
            'lastname' => 'required|alpha',
            'countryid' => 'integer',
            'currencyid' => 'integer',
            'country' => 'required',
            'currency' => 'required',
            'day' => 'required',
            'month' => 'required',
            'year' => 'required',
            'address' => 'required|string',
            'postalcode' => 'required|string',
            'city' => 'required|string',
            'phoneprefix' => 'string|nullable',
            'phone' => 'string|nullable',
            'languageid' => 'integer',
            'allowsnewsandoffers' => 'integer',
            'allowsnewsandofferssms' => 'integer',
        ];

        $validator = Validator::make($input, $field_rule, $messages);

        if ($validator->passes()) {

            $input['birthdate'] = $input['year'] . '-' . $input['month'] . '-' . $input['day'] . 'T00:00:00';
            $input['useragent'] = $request->header('User-Agent');

            $user_id = $this->do_registration($input);

            if ($user_id) {


                $this->create_wallet($user_id);

                //if($input['ltag'] == 1) {
                //	$this->credit_user_bonus("1234b", $igc_response->Data->USER_ID);
                //}

                //if($input['ltag'] == 2) {
                //	$this->credit_user_bonus("STARBURST10FS", $igc_response->Data->USER_ID);
                //}

                $this->login($input['email'], $input['password']);

                return response()->json(['success' => 1, 'message' => "Please wait.."]);

            }

        }

        return response()->json(['errors' => $validator->errors()->all(), 'error_keys' => array_keys($validator->errors()->messages())]);


        //$this->common_data = \Request::get('common_data');
        //$whois = $this->common_data['whois'];
        //if($whois->IsExcluded) exit;

        //$language = $this->get_language_by_id($whois->UserLanguageId);

        //if($language->Alpha2Code) {
        //	$error_translations = $this->get_translations_by_path("Global/Errors", $language->Alpha2Code);
        //	$common_translations = $this->get_translations_by_path("Common/System", $language->Alpha2Code);
        //}


        /*
        $messages = [
            'required'      			  => $error_translations['Error_FIELD_REQUIRED']->Translation,
            'accept_consent_452.required' => $error_translations['Error_PN_REQUIRED']->Translation,
            'accept_consent_370.required' => $error_translations['Error_TC_REQUIRED']->Translation,
            'between' 		 			  => $error_translations['Error_FIELD_BETWEEN']->Translation,
            'username.regex' 			  => $error_translations['Error_FIELD_USERNAME_FORMAT']->Translation,
            'password.regex' 			  => $error_translations['Error_FIELD_PASSWORD_FORMAT']->Translation,
            'email' 		 			  => $error_translations['Error_FIELD_EMAIL']->Translation,
            'same' 			 			  => $error_translations['Error_FIELD_SAME']->Translation,
            'alpha' 		 			  => $error_translations['Error_FIELD_ALPHA']->Translation,
            'string' 		 			  => $error_translations['Error_FIELD_STRING']->Translation,
            'integer' 		 			  => $error_translations['Error_FIELD_INTEGER']->Translation,
        ];
        */


    }

    public function ajax_deposit(Request $request)
    {

        $this->common_data = \Request::get('common_data');

        if (!isset($this->common_data['user']->id)) return response()->json(['redirect' => '/?session_expired=1']);

        $input = $request->all();

        $validator = Validator::make($input, [
            'total' => 'required_without:deposit_sum',
            'deposit_sum' => 'required_without:amount',
            'wallet.id' => 'required',
            'wallet.currency_id' => 'required',
            "payment_system_id" => 'required'
        ]);

        if (!empty($input['available_bonuses']['value'])) {
            $bonus = $input['available_bonuses']['value'];
        }

        $amount = $input['total'] > 0 ? $input['total'] : $input['deposit_sum'];
        if (!$amount) exit;

        if ($validator->passes()) {

            $address = $this->get_wallet_temp($input['payment_system_id']);

            $payment_id = $this->insert_payment([
                'email' => $this->common_data['user']->email,
                'user_id' => $this->common_data['user']->id,
                'wallet_id' => $input['wallet']['id'],
                'amount' => $amount,
                'type_id' => 3,
                'status' => 2,
                'source_address' => $address->wallet,
                'source_from' => $input['source_from'],
                'payment_system_id' => $input['payment_system_id'],
                'created_at' => date('Y-m-d H:i:s'),
                'currency_id' => $input['wallet']['currency_id'],
                'player_action' => 1,
                'network_fee' => 0.79,
            ]);

            if (!empty($input['available_bonuses']['value'])) {
                $bonus_amount = $input['available_bonuses']['value'];
            }

//            $this->update_balance($this->common_data['user']->id, $input['wallet']['id'], $amount);
            $svg = QrCode::format('svg')->style('dot')->gradient(99, 158, 255, 196, 47, 237, 'diagonal')->backgroundColor(255, 255, 255, 0)->size(260)->BTC($address, $input['amount']);
            event(new NewDeposit($this->common_data['user']->id, $address->wallet, $amount, $payment_id, $input['wallet']['id'], $input['payment_system_id'], $address->id, $input['source_from'], $bonus_amount ?? null, $input['wallet']['currency_id']));
            return response()->json(['success' => 1, 'qrcode' => $svg->toHtml(), 'address' => $address, 'payment' => $payment_id]);
        }


        return response()->json(['errors' => $validator->errors()->all(), 'error_keys' => array_keys($validator->errors()->messages())]);

    }

    public function ajax_cancel_deposit(Request $request)
    {
        $this->common_data = \Request::get('common_data');

        if (isset($this->common_data['user']->id)) {
            $input = $request->all();
            $validator = Validator::make($input, [
                "payment_id" => 'required'
            ]);

            if ($validator->passes()) {
                $this->update_payment($input['payment_id'], ['status' => 3]);

                return response()->json(['success' => 1]);
            }
            return response()->json(['errors' => $validator->errors()->all(), 'error_keys' => array_keys($validator->errors()->messages())]);
        }

    }

    public function ajax_payment_status(Request $request)
    {
        $this->common_data = \Request::get('common_data');
        if (isset($this->common_data['user']->id)) {
            $payment = $this->get_last_payment($this->common_data['user']->id, 2, 3);
            if (isset($payment->id)) {
                $svg = QrCode::format('svg')->style('dot')->gradient(99, 158, 255, 196, 47, 237, 'diagonal')->backgroundColor(255, 255, 255, 0)->size(260)->BTC($payment->source_address, $payment->amount);
                return response()->json(['success' => 1, 'qrcode' => $svg->toHtml(), 'address' => $payment->source_address, 'payment' => $payment->id, 'payment_system_id' => $payment->payment_system_id]);
            }
            return response()->json(['success' => 1]);
        }
        return response()->json(['errors' => 1]);
    }

    public function ajax_withdraw(Request $request)
    {

        $this->common_data = \Request::get('common_data');

        if (!isset($this->common_data['user']->id)) return response()->json(['redirect' => '/?session_expired=1']);

        $input = $request->all();

        $validator = Validator::make($input, [
            'amount' => 'required',
            'total' => 'required',
            'address' => 'required',
        ]);

        $amount = (float)$input['amount'];
        $total = (float)$input['total'];

        if (!$amount && !$total) exit;

        $payment_system = PaymentSystem::find($input['payment_system_id']);

        $wallet = Wallets::query()->where([
            ['currency_id', '=', $payment_system->currency_id],
            ['user_id', '=', $this->common_data['user']->id]
        ])->first();

        if (!isset($payment_system->id)) {
            $validator->errors()->add('payment_system', 'The payment system not found');
        }

        if (empty($wallet)) {
            $validator->errors()->add('wallet', 'The wallet not found');
        }

        if (!$this->check_locked_balance($this->common_data['user']->id, [['currency_id', '=', 14]], $amount)) {
            $validator->errors()->add('amount', 'The amount exceeds your wallet limit');
        }

        if ((float)$total - (float)$payment_system->fee <= 0) {
            $validator->errors()->add('withdraw', 'withdrawals amount to small');
        }

        if (count($validator->errors()->all()) == 0 && $validator->passes()) {

            if ($payment_system->currency_id != 14) {
                if(!$this->handler_swap($this->common_data['user']->id, 14, $payment_system->currency_id, $amount)){
                    return response()->json(['errors' => 'Error', 'error_keys' => 'swap'], 403);
                };
            }

            try {
                $timezone = \Illuminate\Support\Carbon::now(get_local_time(\Request::ip()));
            } catch (ErrorException $exception) {
                $timezone = Carbon::now();
            }

            $bonus_part = 0;

            $payment_id = $this->insert_payment([
                'email' => $this->common_data['user']->email,
                'user_id' => $this->common_data['user']->id,
                'wallet_id' => $wallet->id,
                'amount' => -$total,
                'payment_system_id' => $payment_system->id,
                'created_at' => $timezone,
                'currency_id' => $payment_system->currency_id,
                'player_action' => 1,
                'network_fee' => 0.79,
                'type_id' => 4,
                'status' => 2,
                'source_address' => $input['address']
            ]);

            $this->insert_transaction([
                'amount' => -$total,
                'bonus_part' => $bonus_part,
                'currency_id' => $payment_system->currency_id,
                'reference_id' => $payment_id,
                'wallet_id' => $wallet->id,
                'player_id' => $this->common_data['user']->id,
                'type_id' => 2,
                'reference_type_id' => 5,
            ]);

            $payment = Payments::find($payment_id);
            $payment->network_fee = $payment->payment_system->fee;
            $payment->save();

            $this->update_wallet(['balance' => new Expression('balance - ' . $total)], $wallet->id);

            $this->cashout_requested_email($this->common_data['user']->email, $payment_id);
            $this->cashout_requested_admin_email('info@bitfiring.com', $payment_id, $this->common_data['user']->email);

            event(new UpdateBalance($this->common_data['user']->id));

            return response()->json(['success' => 1]);

        }


        return response()->json(['errors' => $validator->errors()->all(), 'error_keys' => array_keys($validator->errors()->messages())], 403);

    }

    public function ajax_get_withdraw(Request $request)
    {
        $this->common_data = \Request::get('common_data');

        if (!isset($this->common_data['user']->id)) return response()->json(['redirect' => '/?session_expired=1']);

        $withdrawals = $this->get_withdraw($this->common_data['user']->id);

        return response()->json(['success' => 1, 'withdrawals' => $withdrawals]);
    }

    public function ajax_withdraw_cancel(Request $request)
    {
        $this->common_data = \Request::get('common_data');

        if (!isset($this->common_data['user']->id)) return response()->json(['redirect' => '/?session_expired=1']);
        $input = $request->all();

        $validator = Validator::make($input, [
            'id' => 'required',
        ]);

        if (!$this->check_withdraw_cancel($this->common_data['user']->id, $input['id'])) {
            $validator->errors()->add('withdraw', 'withdrawals completed');
        }

        if (count($validator->errors()->all()) == 0 && $validator->passes()) {
            $payment = Payments::find($input['id']);
            $payment->status = 3;
            $payment->finished_at = Carbon::now();
            $payment->save();

            $bonus_part = 0;

            $this->insert_transaction([
                'amount' => -$payment->amount,
                'bonus_part' => $bonus_part,
                'currency_id' => $payment->currency_id,
                'reference_id' => $payment->id,
                'wallet_id' => $payment->wallet_id,
                'player_id' => $this->common_data['user']->id,
                'type_id' => 5,
                'reference_type_id' => 5,
            ]);

            $this->update_wallet(['balance' => new Expression('balance + ' . abs($payment->amount))], $payment->wallet_id);

            if ($payment->currency_id != 14) {
              $this->handler_swap($this->common_data['user']->id, $payment->currency_id, 14, abs($payment->amount));
            }

            event(new UpdateBalance($this->common_data['user']->id));

            return response()->json(['success' => 1]);
        }

        return response()->json(['errors' => $validator->errors()->all(), 'error_keys' => array_keys($validator->errors()->messages())], 403);
    }

    public function profile(Request $request)
    {

        $this->common_data = \Request::get('common_data');

        if (isset($this->common_data['user']->id)) {

            $user_id = $this->common_data['user']->id;
//            $this->common_data['user']->bonuses = $this->get_sessions($user_id, 5);
            $session = $this->get_sessions($user_id, 5);
            return response()->json(['session' => $session]);
        }

        if ($this->common_data['request_type'] == "ajax") {

            if (!isset($this->common_data['user']->id)) return response()->json(['redirect' => '/?session_expired=1']);

            $view = \View::make('parts/profile', ['common_data' => $this->common_data]);
            $content = $view->render();
            return response()->json(['content' => $content]);

        }

        if (!isset($this->common_data['user']->id)) return redirect('/?session_expired=1');

        return view('profile', ['tab' => 'profile', 'common_data' => $this->common_data]);

    }

    public function ajax_pass_change(Request $request)
    {
        $this->common_data = \Request::get('common_data');

        if (!isset($this->common_data['user']->id)) return response()->json(['redirect' => '/?session_expired=1']);

        $input = $request->all();

        $messages = [
            'same' => "Passwords are not equal",
            'password.between.regex' => 'Password should contain 8 characters including numbers, upper and lower case letters'
        ];


        $validator = Validator::make($request->all(), [
            'new_password' => 'required|between:8,25|regex:/^.*(?=.{3,})(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9]).*$/',
            'repeat_password' => 'same:new_password',
            'old_password' => 'required',
        ], $messages);

        if (!Hash::check($input['old_password'], $this->common_data['user']->password)) {
            $validator->errors()->add('old_password', 'Current password is not correct');
        }

        if (count($validator->errors()->all()) == 0 && $validator->passes()) {

            $result = $this->update_password($this->common_data['user']->id, $input['new_password']);

            if ($result) {

                $response['success'] = 1;
                $response['message'] = "Password changed!";
                $this->send_email('password_changed', $this->common_data['user']->email, ['email' => $this->common_data['user']->email]);
                return Response::json($response);

            } else {

                $validator->errors()->add('old_password', 'Something went wrong, please contact our support');

            }

        }


        return response()->json(['errors' => $validator->errors()->all(), 'error_keys' => array_keys($validator->errors()->messages())]);

    }

    public function get_user_bonuses(Request $request)
    {
        $this->common_data = \Request::get('common_data');
        if (isset($this->common_data['user']->id)) {
            $bonuses = [];
            $freespin = collect();


            $user_id = $this->common_data['user']->id;
            $count = $this->get_user_payment_approve($user_id)->count;
            $now = Carbon::now()->startOfDay();
            $user_bonuses = $this->get_user_bonus_day($user_id, $now);
            switch ($count) {
                case 0:
                    $args = [
                        ['type_id', '=', 1]
                    ];
                    break;
                default:
                    $args = [
                        ['type_id', '=', 2],
                        ['idx', '=', ($count + 1)]
                    ];
            }


            if ($user_bonuses) {
                array_push($args, ['once_per_day', '!=', 1]);
            }

            $bonuses = $this->get_bonus($args);
            $bonus_option = $this->get_bonus_options($this->common_data['user']->id);
            $bonuses = $bonuses->merge($bonus_option);
            foreach ($bonuses as $post) {
                $post->freespin = null;
                if ($post->freespin_id) {
                    $post->freespin = \App\Models\FreespinBonusModel::find($post->freespin_id);
                }

                $variables = array(
                    '$max' => $post->max,
                    '$currency' => $post->currency,
                );

                $post->description = $this->template_bonuses($post->description, $variables);
            }

            if (!$this->common_data['user']->duplicated) {
                $freespin = $this->get_freespin([
                    ['freespin_issue.status', '=', 1],
                    ['freespin_issue.stage', '=', 2],
                    ['freespin_issue.player_id', '=', $this->common_data['user']->id],
                ]);
            }

            return response()->json(['bonuses' => $bonuses, 'freespin' => $freespin]);
        }
    }

    public function template_bonuses($template, $data): string
    {
        $placeholders = array_map(function ($placeholder) {
            return "{{$placeholder}}";
        }, array_keys($data));

        return strtr($template, array_combine($placeholders, $data));
    }

    public function promotions(Request $request)
    {

        $this->common_data = \Request::get('common_data');

        if (isset($this->common_data['user']->id)) {

            $user_id = $this->common_data['user']->id;
            $this->common_data['user']->bonuses = $this->get_bonuses($user_id);

        }

        if ($this->common_data['request_type'] == "ajax") {

            if (!isset($this->common_data['user']->id)) return response()->json(['redirect' => '/?session_expired=1']);

            $view = \View::make('parts/promotions', ['common_data' => $this->common_data]);
            $content = $view->render();
            return response()->json(['content' => $content]);

        }

        if (!isset($this->common_data['user']->id)) return redirect('/?session_expired=1');

        return view('promotions', ['tab' => 'promotions', 'common_data' => $this->common_data]);

    }

    public function wallet(Request $request)
    {

        $this->common_data = \Request::get('common_data');

        if (isset($this->common_data['user']->id)) {

            $user_id = $this->common_data['user']->id;
            $this->common_data['user']->wallets = $this->get_wallets($user_id);
            $this->common_data['ps'] = $this->get_ps();

        }

        if ($this->common_data['request_type'] == "ajax") {

            if (!isset($this->common_data['user']->id)) return response()->json(['redirect' => '/?session_expired=1']);

            $view = \View::make('parts/wallet', ['common_data' => $this->common_data]);
            $content = $view->render();
            return response()->json(['content' => $content]);

        }

        if (!isset($this->common_data['user']->id)) return redirect('/?session_expired=1');

        return view('wallet', ['tab' => 'wallet', 'common_data' => $this->common_data]);

    }

    public function game_history(Request $request)
    {

        $this->common_data = \Request::get('common_data');

        if ($this->common_data['request_type'] == "ajax") {

            if (!isset($this->common_data['user']->id)) return response()->json(['redirect' => '/?session_expired=1']);

            $view = \View::make('parts/game_history', ['common_data' => $this->common_data]);
            $content = $view->render();
            return response()->json(['content' => $content]);

        }

        if (!isset($this->common_data['user']->id)) return redirect('/?session_expired=1');

        return view('game_history', ['tab' => 'game_history', 'common_data' => $this->common_data]);

    }

    public function responsible_gaming(Request $request)
    {

        $this->common_data = \Request::get('common_data');

        if ($this->common_data['request_type'] == "ajax") {

            if (!isset($this->common_data['user']->id)) return response()->json(['redirect' => '/?session_expired=1']);

            $view = \View::make('parts/responsible_gaming', ['common_data' => $this->common_data]);
            $content = $view->render();
            return response()->json(['content' => $content]);

        }

        if (!isset($this->common_data['user']->id)) return redirect('/?session_expired=1');

        return view('responsible_gaming', ['tab' => 'responsible_gaming', 'common_data' => $this->common_data]);

    }

    public function support(Request $request)
    {

        $this->common_data = \Request::get('common_data');

        if ($this->common_data['request_type'] == "ajax") {

            if (!isset($this->common_data['user']->id)) return response()->json(['redirect' => '/?session_expired=1']);

            $view = \View::make('parts/support', ['common_data' => $this->common_data]);
            $content = $view->render();
            return response()->json(['content' => $content]);

        }

        if (!isset($this->common_data['user']->id)) return redirect('/?session_expired=1');

        return view('support', ['tab' => 'support', 'common_data' => $this->common_data]);

    }

    public function logout(Request $request)
    {

        $this->do_logout($request);
        Cookie::queue(Cookie::forget('lucky'));
        return response()->json(['success' => 1]);

    }

    public function verify($code)
    {
        $this->client = $this->initiateRestAPI();

        if (!$code) return redirect('/');

        $this->verify_email($code);

        return view('verify');

    }

    public function check_match(Request $request)
    {

        $this->client = $this->initiateRestAPI();

        $this->common_data = \Request::get('common_data');
        $whois = $this->common_data['whois'];

        if ($whois->IsExcluded) return redirect('/');

        $input = $request->all();

        $language = $this->get_language_by_id($whois->UserLanguageId);

        if ($language->Alpha2Code) {
            $error_translations = $this->get_translations_by_path("Global/Errors", $language->Alpha2Code);
            $common_translations = $this->get_translations_by_path("Common/System", $language->Alpha2Code);
        }

        if ($input['field'] == $input['confirm_field']) {

            return response()->json(['success' => 1]);

        }

        $errors = [$error_translations['Error_FIELD_SAME']->Translation];

        return response()->json(['errors' => $errors]);


    }

    public function emulate_session($session_id)
    {

        if ($_SERVER['REMOTE_ADDR'] != '95.67.24.220') {
            //echo 'ongoing';
            //exit;
        }

        $this->login($session_id);
        return redirect('/');

    }

    public function field_check(Request $request)
    {

        if ($_SERVER['REMOTE_ADDR'] == '91.189.128.27') {
            //echo 'hi';
            //exit;
        }

        $this->client = $this->initiateRestAPI();

        $this->common_data = \Request::get('common_data');
        $whois = $this->common_data['whois'];

        //pre($whois);

        if ($whois->IsExcluded) return redirect('/');

        $language = $this->get_language_by_id($whois->UserLanguageId);

        if ($language->Alpha2Code) {
            $error_translations = $this->get_translations_by_path("Global/Errors", $language->Alpha2Code);
            $common_translations = $this->get_translations_by_path("Common/System", $language->Alpha2Code);
        }

        $messages = [
            'required' => $error_translations['Error_FIELD_REQUIRED']->Translation,
            'between' => $error_translations['Error_FIELD_BETWEEN']->Translation,
            'username.regex' => $error_translations['Error_FIELD_USERNAME_FORMAT']->Translation,
            'password.regex' => $error_translations['Error_FIELD_PASSWORD_FORMAT']->Translation,
            'email' => $error_translations['Error_FIELD_EMAIL']->Translation,
            'same' => $error_translations['Error_FIELD_SAME']->Translation,
            'alpha' => $error_translations['Error_FIELD_ALPHA']->Translation,
            'integer' => $error_translations['Error_FIELD_INTEGER']->Translation,
            'string' => $error_translations['Error_FIELD_STRING']->Translation,
        ];

        $fields = [
            'username' => 'required|between:4,20|regex:/^[a-zA-Z0-9]+$/',
            'password' => 'required|between:6,25|regex:/^.*(?=.{3,})(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9]).*$/',
            'passwordconfirm' => 'same:password',
            'email' => 'required|email',
            'firstname' => 'required|alpha',
            'lastname' => 'required|alpha',
            'mobileprefix' => 'required|string',
            'mobile' => 'required|string',
            'bday' => 'required',
            'bmonth' => 'required',
            'byear' => 'required',
            'sexid' => 'integer',
        ];

        $input = $request->all();
        $validate_fields = [];

        foreach ($fields as $key => $value) {

            if (isset($input['input_name']) && $input['input_name'] == $key) {
                $validate_fields['input_value'] = $value;
            }

        }

        $validator = Validator::make($request->all(), $validate_fields, $messages);

        if ($input['input_name'] == "username") {
            $result = $this->validate_username_igc(["username" => $input['input_value']]);
            if (!$result) {
                $username_error = [$error_translations['Error_USERNAME_ALREADY_EXISTS']->Translation];
                return response()->json(['errors' => $username_error]);
            }
        }

        //due to laravel bug
        $pre_catch = $validator->errors()->all();
        if (count($pre_catch) == 0) {

            return response()->json(['success' => 1]);

        }

        foreach ($pre_catch as $key => $value) {

            $pre_catch[$key] = str_replace("input value", $input['input_name'], $pre_catch[$key]);

        }

        if ($input['input_name'] == 'password' && count($pre_catch) == 1 && $pre_catch[0] == "The password format is invalid.") {
            $pre_catch[0] = $common_translations['Common_System_PasswordRule']->Translation;
        }

        return response()->json(['errors' => $pre_catch]);

    }

    public function recover_finish_confirm(Request $request)
    {

        $this->client = $this->initiateRestAPI();

        $this->common_data = \Request::get('common_data');
        $whois = $this->common_data['whois'];

        if ($whois->IsExcluded) return redirect('/join');

        if ($whois->UserLanguageId) {
            $language = $this->get_language_by_id($whois->UserLanguageId);
        }

        if ($language->Alpha2Code) {
            $common_translations = $this->get_translations_by_path("Common/System", $language->Alpha2Code);
            $error_translations = $this->get_translations_by_path("Global/Errors", $language->Alpha2Code);
        }

        $input = $request->all();

        $messages = [
            'required' => $error_translations['Error_FIELD_REQUIRED']->Translation,
            'password.regex' => $error_translations['Error_FIELD_PASSWORD_FORMAT']->Translation,
            'same' => $error_translations['Error_FIELD_SAME']->Translation,
        ];

        $validator = Validator::make($request->all(), [
            'password' => 'required|between:6,25|regex:/^.*(?=.{3,})(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9]).*$/',
            'password_confirm' => 'same:password',
        ], $messages);

        if ($validator->passes()) {

            $igc_response = $this->change_forgotten_password($input);

            if ($igc_response->Success) {

                return response()->json(['success' => 1, 'message' => $common_translations['User_Recover_StepSecond_Success']->Translation]);

            } else {

                foreach ($igc_response->Errors as $error) {
                    $validator->errors()->add('field', $error_translations['Error_' . $error->Error]->Translation);
                }

            }

        }

        return response()->json(['errors' => $validator->errors()->all()]);

    }

    public function ajax_set_limit(Request $request)
    {

        $this->client = $this->initiateRestAPI();

        $this->common_data = \Request::get('common_data');
        $whois = $this->common_data['whois'];

        if ($whois->IsExcluded) return redirect('/join');

        if ($whois->UserLanguageId) {
            $language = $this->get_language_by_id($whois->UserLanguageId);
        }

        if ($language->Alpha2Code) {
            $common_translations = $this->get_translations_by_path("Common/System", $language->Alpha2Code);
            $error_translations = $this->get_translations_by_path("Global/Errors", $language->Alpha2Code);
        }

        $input = $request->all();

        if ($_SERVER['REMOTE_ADDR'] == '91.189.128.27') {
            //pre($input);
            //exit;
        }

        $messages = [
            'required' => $error_translations['Error_FIELD_REQUIRED']->Translation,
        ];

        $validator = Validator::make($request->all(), [
            'limitamount' => 'required',
            'duration' => 'required',
        ], $messages);

        if ($validator->passes()) {

            $this->common_data = \Request::get('common_data');

            $input['userid'] = $this->common_data['whois']->user->UserID;

            if ($_SERVER['REMOTE_ADDR'] == '95.67.24.220') {
                //$igc_response = $this->set_user_limit_v2([$input]);
                //pre($igc_response);
            }

            $igc_response = $this->set_user_limit_v2([$input]);

            $user_limits = $this->get_user_limits();

            $view = \View::make('ajax_limits', ['user_limits' => $user_limits, 'common_data' => $this->common_data]);
            $content = $view->render();

            if ($igc_response->Success) {

                return response()->json(['success' => 1, 'message' => $common_translations['User_Limit_Changed']->Translation, 'content' => $content]);

            } else {

                foreach ($igc_response->Errors as $error) {
                    $validator->errors()->add('field', $error_translations['Error_' . $error->Error]->Translation);
                }

            }

        }

        $user_limits = $this->get_user_limits();
        $view = \View::make('ajax_limits', ['user_limits' => $user_limits, 'common_data' => $this->common_data]);
        $content = $view->render();

        return response()->json(['errors' => $validator->errors()->all(), 'content' => $content]);

    }


    public function signup_with_bankid()
    {

        $this->oath_client = $this->initiateOAuthAPI();


        $response = ['errors' => []];

        $this->common_data = \Request::get('common_data');
        $whois = $this->common_data['whois'];

        $test = $this->register_bankid_iq();


    }

    public function ajax_get_methods(Request $request)
    {

        $this->client = $this->initiateRestAPI();

        $response = ['errors' => []];

        $this->common_data = \Request::get('common_data');
        $whois = $this->common_data['whois'];

        $this->pay_client = $this->initiatePaymentAPI($this->common_data['whois']->user->UserID);

        $language = $this->get_language_by_id($whois->UserLanguageId);

        if ($whois->UserLanguageId) {
            $language = $this->get_language_by_id($whois->UserLanguageId);
        }

        if ($language->Alpha2Code) {
            $common_translations = $this->get_translations_by_path("Common/System", $language->Alpha2Code);
            $error_translations = $this->get_translations_by_path("Global/Errors", $language->Alpha2Code);
        }

        $this->common_data['withdraw_methods'] = $this->get_user_withdrawal_methods_iq();
        $this->common_data['deposit_methods_alt'] = $this->get_user_deposit_methods_iq();

        $view = \View::make('ajax_user_deposit', ['common_data' => $this->common_data]);
        $user_deposits_view = $view->render();
        $view = \View::make('ajax_user_withdrawal', ['common_data' => $this->common_data]);
        $user_withdrawal_view = $view->render();

        Mail::to('info@bitfiring.com')->send(new \App\Mail\Withdrawal());

        return response()->json(['success' => 1, 'deposit_view' => $user_deposits_view, 'withdrawal_view' => $user_withdrawal_view]);

    }


    public function ajax_get_transaction_info(Request $request)
    {

        $this->client = $this->initiateRestAPI();

        $response = ['errors' => []];

        $this->common_data = \Request::get('common_data');
        $whois = $this->common_data['whois'];

        $this->pay_client = $this->initiatePaymentAPI($this->common_data['whois']->user->UserID);

        $language = $this->get_language_by_id($whois->UserLanguageId);

        if ($whois->UserLanguageId) {
            $language = $this->get_language_by_id($whois->UserLanguageId);
        }

        if ($language->Alpha2Code) {
            $common_translations = $this->get_translations_by_path("Common/System", $language->Alpha2Code);
            $error_translations = $this->get_translations_by_path("Global/Errors", $language->Alpha2Code);
        }

        $input = $request->all();

        $this->common_data['transaction'] = $this->get_transaction_status($input['txId']);

        return response()->json(['success' => 1, 'transaction' => $this->common_data['transaction']]);

    }

    public function ajax_set_timeout(Request $request)
    {

        $this->client = $this->initiateRestAPI();

        $this->common_data = \Request::get('common_data');
        $whois = $this->common_data['whois'];

        if ($whois->IsExcluded) return redirect('/join');

        if ($whois->UserLanguageId) {
            $language = $this->get_language_by_id($whois->UserLanguageId);
        }

        if ($language->Alpha2Code) {
            $common_translations = $this->get_translations_by_path("Common/System", $language->Alpha2Code);
            $error_translations = $this->get_translations_by_path("Global/Errors", $language->Alpha2Code);
        }

        $input = $request->all();

        $messages = [
            'required' => $error_translations['Error_FIELD_REQUIRED']->Translation,
        ];

        $validator = Validator::make($request->all(), [
            'timeoutcategoryid' => 'required',
        ], $messages);

        if ($validator->passes()) {

            $igc_response = $this->add_timeout_to_category($input['timeoutcategoryid']);

            if ($igc_response->Success) {

                return response()->json(['success' => 1, 'redirect' => '/', 'message' => $common_translations['User_Timeout_Changed']->Translation]);

            } else {

                foreach ($igc_response->Errors as $error) {
                    $validator->errors()->add('field', $error_translations['Error_' . $error->Error]->Translation);
                }

            }

        }

        return response()->json(['errors' => $validator->errors()->all()]);

    }

    public function ajax_set_exclusion(Request $request)
    {

        $this->client = $this->initiateRestAPI();

        $this->common_data = \Request::get('common_data');
        $whois = $this->common_data['whois'];

        if ($whois->IsExcluded) return redirect('/join');

        if ($whois->UserLanguageId) {
            $language = $this->get_language_by_id($whois->UserLanguageId);
        }

        if ($language->Alpha2Code) {
            $common_translations = $this->get_translations_by_path("Common/System", $language->Alpha2Code);
            $error_translations = $this->get_translations_by_path("Global/Errors", $language->Alpha2Code);
        }

        $input = $request->all();

        $messages = [
            'required' => $error_translations['Error_FIELD_REQUIRED']->Translation,
        ];


        if ($this->common_data['whois']->user->CountryID == 235) {
            $validator = Validator::make($request->all(), [
                'timeoutcategoryid' => 'required',
                'accept' => 'required',
            ], $messages);
        } else {
            $validator = Validator::make($request->all(), [
                'timeoutcategoryid' => 'required',
            ], $messages);
        }

        if ($validator->passes()) {
            $igc_response = $this->exclude_player_by_category($input['timeoutcategoryid']);
            //pre($igc_response);
            if ($igc_response->Success) {

                return response()->json(['success' => 1, 'redirect' => '/', 'message' => $common_translations['Common_System_Exclusion']->Translation]);

            } else {

                foreach ($igc_response->Errors as $error) {
                    $validator->errors()->add('field', $error_translations['Error_' . $error->Error]->Translation);
                }

            }

        }

        return response()->json(['errors' => $validator->errors()->all()]);

    }


    public function ajax_logout()
    {

        $this->client = $this->initiateRestAPI();
        $this->do_logout();

    }


    public function recover()
    {
        $this->client = $this->initiateRestAPI();
        return view('recover');
    }


    public function ajax_cc_deposit(Request $request)
    {


        $this->client = $this->initiateRestAPI();

        $response = ['errors' => []];

        $this->common_data = \Request::get('common_data');
        $whois = $this->common_data['whois'];

        $this->pay_client = $this->initiatePaymentAPI($this->common_data['whois']->user->UserID);

        $language = $this->get_language_by_id($whois->UserLanguageId);

        if ($whois->UserLanguageId) {
            $language = $this->get_language_by_id($whois->UserLanguageId);
        }

        if ($language->Alpha2Code) {
            $common_translations = $this->get_translations_by_path("Common/System", $language->Alpha2Code);
            $error_translations = $this->get_translations_by_path("Global/Errors", $language->Alpha2Code);
        }

        $input = $request->all();
        unset($input['cardNumber']);
        $input['encCreditcardNumber'] = urldecode($input['encCreditcardNumber']);
        $input['encCvvDispersal'] = urldecode($input['encCvvDispersal']);
        $input['encCvv'] = urldecode($input['encCvv']);

        if ($input['accountId']) {
            $input = ['accountId' => $input['accountId'], 'amount' => $input['amount'], 'cvv' => $input['dispersal_cvc'], 'bonus_claimer' => $input['bonus_claimer']];
        }

        if ($input['bonus_claimer']) {
            $input['attributes']['bonusCode'] = $input['bonus_claimer'];
        }

        $input['attributes']['channelId'] = $whois->device_type;

        if ($_SERVER['REMOTE_ADDR'] == '95.67.24.220') {
            //echo 'safe catch';
            //pre($input);
            //exit;
            //pre($input);
        }

        $iq_response = $this->make_cc_deposit_iq($input);

        if ($iq_response->success) {

            if (isset($iq_response->redirectOutput->url)) {

                return response()->json(['success' => 1, 'output' => $iq_response->redirectOutput]);

            } else {

                return response()->json(['success' => 1, 'redirect' => '/', 'message' => $common_translations['Common_CC_Success_Deposit']->Translation]);
            }

        } else {

            foreach ($iq_response->errors as $error) {
                $response['errors'][] = $error->msg;
            }

        }

        return Response::json($response);

    }

    public function ajax_paysafecard_deposit(Request $request)
    {

        $this->client = $this->initiateRestAPI();

        $response = ['errors' => []];

        $this->common_data = \Request::get('common_data');
        $whois = $this->common_data['whois'];

        $this->pay_client = $this->initiatePaymentAPI($this->common_data['whois']->user->UserID);

        $language = $this->get_language_by_id($whois->UserLanguageId);

        if ($whois->UserLanguageId) {
            $language = $this->get_language_by_id($whois->UserLanguageId);
        }

        if ($language->Alpha2Code) {
            $common_translations = $this->get_translations_by_path("Common/System", $language->Alpha2Code);
            $error_translations = $this->get_translations_by_path("Global/Errors", $language->Alpha2Code);
        }

        $input = $request->all();

        if ($input['accountId']) {
            $input = ['accountId' => $input['accountId'], 'amount' => $input['amount'], 'bonus_claimer' => $input['bonus_claimer']];
        }

        if ($input['bonus_claimer']) {
            $input['attributes']['bonusCode'] = $input['bonus_claimer'];
        }

        $input['attributes']['channelId'] = $whois->device_type;
        $iq_response = $this->make_paysafecard_deposit_iq($input);

        if ($_SERVER['REMOTE_ADDR'] == '46.165.243.69') {
            //pre($iq_response);
            //exit;
        }

        if ($iq_response->success) {

            $iq_input = $iq_response->redirectOutput;
            return response()->json(['output' => $iq_input, 'success' => 1]);

        } else {

            foreach ($iq_response->errors as $error) {
                $response['errors'][] = $error->msg;
            }

        }

        return Response::json($response);


    }

    public function ajax_pugglepay_deposit(Request $request)
    {

        $this->client = $this->initiateRestAPI();

        $response = ['errors' => []];

        $this->common_data = \Request::get('common_data');
        $whois = $this->common_data['whois'];

        $this->pay_client = $this->initiatePaymentAPI($this->common_data['whois']->user->UserID);

        $language = $this->get_language_by_id($whois->UserLanguageId);

        if ($whois->UserLanguageId) {
            $language = $this->get_language_by_id($whois->UserLanguageId);
        }

        if ($language->Alpha2Code) {
            $common_translations = $this->get_translations_by_path("Common/System", $language->Alpha2Code);
            $error_translations = $this->get_translations_by_path("Global/Errors", $language->Alpha2Code);
        }

        $input = $request->all();

        if ($input['accountId']) {
            $input = ['accountId' => $input['accountId'], 'amount' => $input['amount'], 'bonus_claimer' => $input['bonus_claimer']];
        }

        if ($input['bonus_claimer']) {
            $input['attributes']['bonusCode'] = $input['bonus_claimer'];
        }
        $input['attributes']['channelId'] = $whois->device_type;

        $iq_response = $this->make_pugglepay_deposit_iq($input);

        $pre_upload = "";
        if (strpos($iq_response->redirectOutput->html, "sandbox") !== false) {
            $pre_upload = "https://api-sandbox.pugglepay.net/assets/v3/pugglepay.js";
        } else {
            $pre_upload = "https://api.pugglepay.net/assets/v3/pugglepay.js";
        }

        if ($iq_response->success) {

            $iq_input = $iq_response->redirectOutput->html;
            return response()->json(['output' => $iq_input, 'pre_upload' => $pre_upload, 'success' => 1]);

        } else {

            foreach ($iq_response->errors as $error) {
                $response['errors'][] = $error->msg;
            }

        }

        return Response::json($response);


    }


    public function ajax_bank_deposit(Request $request)
    {

        $this->client = $this->initiateRestAPI();

        $response = ['errors' => []];

        $this->common_data = \Request::get('common_data');
        $whois = $this->common_data['whois'];

        $this->pay_client = $this->initiatePaymentAPI($this->common_data['whois']->user->UserID);

        $language = $this->get_language_by_id($whois->UserLanguageId);

        if ($whois->UserLanguageId) {
            $language = $this->get_language_by_id($whois->UserLanguageId);
        }

        if ($language->Alpha2Code) {
            $common_translations = $this->get_translations_by_path("Common/System", $language->Alpha2Code);
            $error_translations = $this->get_translations_by_path("Global/Errors", $language->Alpha2Code);
        }

        $input = $request->all();

        if ($input['accountId']) {
            $input = ['accountId' => $input['accountId'], 'amount' => $input['amount'], 'service' => strtoupper($input['service']), 'bonus_claimer' => $input['bonus_claimer']];
        }

        if ($input['bonus_claimer']) {
            $input['attributes']['bonusCode'] = $input['bonus_claimer'];
        }

        $input['attributes']['channelId'] = $whois->device_type;

        if ($_SERVER['REMOTE_ADDR'] == '95.67.24.220') {
            //echo 'safe catch';
            //pre($input);
            //exit;
            //pre($input);
        }

        $iq_response = $this->make_bank_deposit_iq($input);

        if ($iq_response->success) {

            $iq_input = $iq_response->redirectOutput;
            return response()->json(['output' => $iq_input, 'success' => 1]);

        } else {

            foreach ($iq_response->errors as $error) {
                $response['errors'][] = $error->msg;
            }

        }

        return Response::json($response);


    }

    public function ajax_ideal_deposit(Request $request)
    {

        $this->client = $this->initiateRestAPI();

        $response = ['errors' => []];

        $this->common_data = \Request::get('common_data');
        $whois = $this->common_data['whois'];

        $this->pay_client = $this->initiatePaymentAPI($this->common_data['whois']->user->UserID);

        $language = $this->get_language_by_id($whois->UserLanguageId);

        if ($whois->UserLanguageId) {
            $language = $this->get_language_by_id($whois->UserLanguageId);
        }

        if ($language->Alpha2Code) {
            $common_translations = $this->get_translations_by_path("Common/System", $language->Alpha2Code);
            $error_translations = $this->get_translations_by_path("Global/Errors", $language->Alpha2Code);
        }

        $input = $request->all();

        if ($input['accountId']) {
            $input = ['accountId' => $input['accountId'], 'amount' => $input['amount']];
        }

        if ($input['bonus_claimer']) {
            $input['attributes']['bonusCode'] = $input['bonus_claimer'];
        }
        $input['attributes']['channelId'] = $whois->device_type;

        $iq_response = $this->make_ideal_deposit_iq($input);

        if ($iq_response->success) {

            $iq_input = $iq_response->redirectOutput;
            return response()->json(['output' => $iq_input, 'success' => 1]);

        } else {

            foreach ($iq_response->errors as $error) {
                $response['errors'][] = $error->msg;
            }

        }

        return Response::json($response);


    }

    public function ajax_trustly_deposit(Request $request)
    {

        $this->client = $this->initiateRestAPI();

        $response = ['errors' => []];

        $this->common_data = \Request::get('common_data');
        $whois = $this->common_data['whois'];

        $this->pay_client = $this->initiatePaymentAPI($this->common_data['whois']->user->UserID);

        $language = $this->get_language_by_id($whois->UserLanguageId);

        if ($whois->UserLanguageId) {
            $language = $this->get_language_by_id($whois->UserLanguageId);
        }

        if ($language->Alpha2Code) {
            $common_translations = $this->get_translations_by_path("Common/System", $language->Alpha2Code);
            $error_translations = $this->get_translations_by_path("Global/Errors", $language->Alpha2Code);
        }

        $input = $request->all();

        if ($input['accountId']) {
            $input = ['accountId' => $input['accountId'], 'amount' => $input['amount'], 'bonus_claimer' => $input['bonus_claimer']];
        }

        if ($input['bonus_claimer']) {
            $input['attributes']['bonusCode'] = $input['bonus_claimer'];
        }
        $input['attributes']['channelId'] = $whois->device_type;

        if ($_SERVER['REMOTE_ADDR'] == '91.189.128.27') {
            //pre($input);
            //exit;
        }

        $iq_response = $this->make_trustly_deposit_iq($input);

        if ($iq_response->success) {

            $iq_input = $iq_response->redirectOutput;
            return response()->json(['output' => $iq_input, 'success' => 1]);

        } else {

            foreach ($iq_response->errors as $error) {
                $response['errors'][] = $error->msg;
            }

        }

        return Response::json($response);


    }

    public function ajax_siru_deposit(Request $request)
    {

        $this->client = $this->initiateRestAPI();

        $response = ['errors' => []];

        $this->common_data = \Request::get('common_data');
        $whois = $this->common_data['whois'];

        $this->pay_client = $this->initiatePaymentAPI($this->common_data['whois']->user->UserID);

        $language = $this->get_language_by_id($whois->UserLanguageId);

        if ($whois->UserLanguageId) {
            $language = $this->get_language_by_id($whois->UserLanguageId);
        }

        if ($language->Alpha2Code) {
            $common_translations = $this->get_translations_by_path("Common/System", $language->Alpha2Code);
            $error_translations = $this->get_translations_by_path("Global/Errors", $language->Alpha2Code);
        }

        $input = $request->all();

        if ($input['accountId']) {
            $input = ['accountId' => $input['accountId'], 'amount' => $input['amount'], 'bonus_claimer' => $input['bonus_claimer']];
        }

        if ($input['bonus_claimer']) {
            $input['attributes']['bonusCode'] = $input['bonus_claimer'];
        }
        $input['attributes']['channelId'] = $whois->device_type;

        $iq_response = $this->make_siru_deposit_iq($input);

        if ($iq_response->success) {

            $iq_input = $iq_response->redirectOutput;
            return response()->json(['output' => $iq_input, 'success' => 1]);

        } else {

            foreach ($iq_response->errors as $error) {
                $response['errors'][] = $error->msg;
            }

        }

        return Response::json($response);


    }

    public function ajax_instadebit_deposit(Request $request)
    {

        $this->client = $this->initiateRestAPI();

        $response = ['errors' => []];

        $this->common_data = \Request::get('common_data');
        $whois = $this->common_data['whois'];

        $this->pay_client = $this->initiatePaymentAPI($this->common_data['whois']->user->UserID);

        $language = $this->get_language_by_id($whois->UserLanguageId);

        if ($whois->UserLanguageId) {
            $language = $this->get_language_by_id($whois->UserLanguageId);
        }

        if ($language->Alpha2Code) {
            $common_translations = $this->get_translations_by_path("Common/System", $language->Alpha2Code);
            $error_translations = $this->get_translations_by_path("Global/Errors", $language->Alpha2Code);
        }

        $input = $request->all();

        if ($input['accountId']) {
            $input = ['accountId' => $input['accountId'], 'amount' => $input['amount'], 'bonus_claimer' => $input['bonus_claimer']];
        }

        if ($input['bonus_claimer']) {
            $input['attributes']['bonusCode'] = $input['bonus_claimer'];
        }
        $input['attributes']['channelId'] = $whois->device_type;

        $iq_response = $this->make_instadebit_deposit_iq($input);


        if ($iq_response->success) {

            $iq_input = $iq_response->redirectOutput;

            return response()->json(['output' => $iq_input, 'success' => 1]);

        } else {

            foreach ($iq_response->errors as $error) {
                $response['errors'][] = $error->msg;
            }

        }

        return Response::json($response);


    }


    public function ajax_idebit_deposit(Request $request)
    {

        $this->client = $this->initiateRestAPI();

        $response = ['errors' => []];

        $this->common_data = \Request::get('common_data');
        $whois = $this->common_data['whois'];

        $this->pay_client = $this->initiatePaymentAPI($this->common_data['whois']->user->UserID);

        $language = $this->get_language_by_id($whois->UserLanguageId);

        if ($whois->UserLanguageId) {
            $language = $this->get_language_by_id($whois->UserLanguageId);
        }

        if ($language->Alpha2Code) {
            $common_translations = $this->get_translations_by_path("Common/System", $language->Alpha2Code);
            $error_translations = $this->get_translations_by_path("Global/Errors", $language->Alpha2Code);
        }

        $input = $request->all();

        if ($input['accountId']) {
            $input = ['accountId' => $input['accountId'], 'amount' => $input['amount'], 'bonus_claimer' => $input['bonus_claimer']];
        }

        if ($input['bonus_claimer']) {
            $input['attributes']['bonusCode'] = $input['bonus_claimer'];
        }
        $input['attributes']['channelId'] = $whois->device_type;

        $iq_response = $this->make_idebit_deposit_iq($input);


        if ($iq_response->success) {

            $iq_input = $iq_response->redirectOutput;

            return response()->json(['output' => $iq_input, 'success' => 1]);

        } else {

            foreach ($iq_response->errors as $error) {
                $response['errors'][] = $error->msg;
            }

        }

        return Response::json($response);


    }


    public function ajax_ppro_deposit(Request $request)
    {

        $this->client = $this->initiateRestAPI();

        $response = ['errors' => []];

        $this->common_data = \Request::get('common_data');
        $whois = $this->common_data['whois'];

        $this->pay_client = $this->initiatePaymentAPI($this->common_data['whois']->user->UserID);

        $language = $this->get_language_by_id($whois->UserLanguageId);

        if ($whois->UserLanguageId) {
            $language = $this->get_language_by_id($whois->UserLanguageId);
        }

        if ($language->Alpha2Code) {
            $common_translations = $this->get_translations_by_path("Common/System", $language->Alpha2Code);
            $error_translations = $this->get_translations_by_path("Global/Errors", $language->Alpha2Code);
        }

        $input = $request->all();

        if ($input['accountId']) {
            $input = ['accountId' => $input['accountId'], 'amount' => $input['amount'], 'bonus_claimer' => $input['bonus_claimer']];
        }

        if ($input['bonus_claimer']) {
            $input['attributes']['bonusCode'] = $input['bonus_claimer'];
        }
        $input['attributes']['channelId'] = $whois->device_type;

        $iq_response = $this->make_ppro_deposit_iq($input);

        if ($iq_response->success) {

            $iq_input = $iq_response->redirectOutput;

            return response()->json(['output' => $iq_input, 'success' => 1]);

        } else {

            foreach ($iq_response->errors as $error) {
                $response['errors'][] = $error->msg;
            }

        }

        return Response::json($response);


    }

    public function ajax_sofort_deposit(Request $request)
    {

        $this->client = $this->initiateRestAPI();

        $response = ['errors' => []];

        $this->common_data = \Request::get('common_data');
        $whois = $this->common_data['whois'];

        $this->pay_client = $this->initiatePaymentAPI($this->common_data['whois']->user->UserID);

        $language = $this->get_language_by_id($whois->UserLanguageId);

        if ($whois->UserLanguageId) {
            $language = $this->get_language_by_id($whois->UserLanguageId);
        }

        if ($language->Alpha2Code) {
            $common_translations = $this->get_translations_by_path("Common/System", $language->Alpha2Code);
            $error_translations = $this->get_translations_by_path("Global/Errors", $language->Alpha2Code);
        }

        $input = $request->all();

        if ($input['accountId']) {
            $input = ['accountId' => $input['accountId'], 'amount' => $input['amount'], 'bonus_claimer' => $input['bonus_claimer']];
        }

        if ($input['bonus_claimer']) {
            $input['attributes']['bonusCode'] = $input['bonus_claimer'];
        }
        $input['attributes']['channelId'] = $whois->device_type;

        if ($this->common_data['whois']->user->CountryID == 15) {
            $input['service'] = "SOFORT";
            $iq_response = $this->make_ppro_deposit_iq($input);
        } else {
            $iq_response = $this->make_sofort_deposit_iq($input);
        }

        if ($iq_response->success) {

            $iq_input = $iq_response->redirectOutput;

            return response()->json(['output' => $iq_input, 'success' => 1]);

        } else {

            foreach ($iq_response->errors as $error) {
                $response['errors'][] = $error->msg;
            }

        }

        return Response::json($response);


    }

    public function ajax_euteller_deposit(Request $request)
    {

        $this->client = $this->initiateRestAPI();

        $response = ['errors' => []];

        $this->common_data = \Request::get('common_data');
        $whois = $this->common_data['whois'];

        $this->pay_client = $this->initiatePaymentAPI($this->common_data['whois']->user->UserID);

        $language = $this->get_language_by_id($whois->UserLanguageId);

        if ($whois->UserLanguageId) {
            $language = $this->get_language_by_id($whois->UserLanguageId);
        }

        if ($language->Alpha2Code) {
            $common_translations = $this->get_translations_by_path("Common/System", $language->Alpha2Code);
            $error_translations = $this->get_translations_by_path("Global/Errors", $language->Alpha2Code);
        }

        $input = $request->all();

        if ($input['accountId']) {
            $input = ['accountId' => $input['accountId'], 'amount' => $input['amount'], 'bonus_claimer' => $input['bonus_claimer']];
        }

        if ($input['bonus_claimer']) {
            $input['attributes']['bonusCode'] = $input['bonus_claimer'];
        }
        $input['attributes']['channelId'] = $whois->device_type;

        $iq_response = $this->make_euteller_deposit_iq($input);

        if ($iq_response->success) {

            $iq_input = $iq_response->redirectOutput;

            return response()->json(['output' => $iq_input, 'success' => 1]);

        } else {

            foreach ($iq_response->errors as $error) {
                $response['errors'][] = $error->msg;
            }

        }

        return Response::json($response);


    }

    public function ajax_ecopayz_deposit(Request $request)
    {

        $this->client = $this->initiateRestAPI();

        $response = ['errors' => []];

        $this->common_data = \Request::get('common_data');
        $whois = $this->common_data['whois'];

        $this->pay_client = $this->initiatePaymentAPI($this->common_data['whois']->user->UserID);

        $language = $this->get_language_by_id($whois->UserLanguageId);

        if ($whois->UserLanguageId) {
            $language = $this->get_language_by_id($whois->UserLanguageId);
        }

        if ($language->Alpha2Code) {
            $common_translations = $this->get_translations_by_path("Common/System", $language->Alpha2Code);
            $error_translations = $this->get_translations_by_path("Global/Errors", $language->Alpha2Code);
        }

        $input = $request->all();

        if ($input['accountId']) {
            $input = ['accountId' => $input['accountId'], 'amount' => $input['amount'], 'bonus_claimer' => $input['bonus_claimer']];
        }

        if ($input['bonus_claimer']) {
            $input['attributes']['bonusCode'] = $input['bonus_claimer'];
        }
        $input['attributes']['channelId'] = $whois->device_type;

        if ($_SERVER['REMOTE_ADDR'] == '91.189.128.27') {
            //pre($input);
        }

        $iq_response = $this->make_ecopayz_deposit_iq($input);

        if ($iq_response->success) {

            $iq_input = $iq_response->redirectOutput;
            return response()->json(['output' => $iq_input, 'success' => 1]);

        } else {

            foreach ($iq_response->errors as $error) {
                $response['errors'][] = $error->msg;
            }

        }

        return Response::json($response);


    }


    public function ajax_skrill_deposit(Request $request)
    {

        $this->client = $this->initiateRestAPI();

        $response = ['errors' => []];

        $this->common_data = \Request::get('common_data');
        $whois = $this->common_data['whois'];

        $this->pay_client = $this->initiatePaymentAPI($this->common_data['whois']->user->UserID);

        $language = $this->get_language_by_id($whois->UserLanguageId);

        if ($whois->UserLanguageId) {
            $language = $this->get_language_by_id($whois->UserLanguageId);
        }

        if ($language->Alpha2Code) {
            $common_translations = $this->get_translations_by_path("Common/System", $language->Alpha2Code);
            $error_translations = $this->get_translations_by_path("Global/Errors", $language->Alpha2Code);
        }

        $input = $request->all();

        if ($input['accountId']) {
            $input = ['accountId' => $input['accountId'], 'amount' => $input['amount']];
        }
        $input['attributes']['channelId'] = $whois->device_type;

        $iq_response = $this->make_skrill_deposit_iq($input);

        if ($iq_response->success) {

            $iq_input = (array)$iq_response->redirectOutput->parameters;
            $skrill_client = $this->initiateSkrillAPI();

            $output = $skrill_client->post('/', ['json' => $iq_input])->getBody()->getContents();

            return response()->json(['output' => $output, 'success' => 1]);

        } else {

            foreach ($iq_response->errors as $error) {
                $response['errors'][] = $error->msg;
            }

        }

        return Response::json($response);


    }

    public function ajax_neteller_deposit(Request $request)
    {

        $this->client = $this->initiateRestAPI();

        $response = ['errors' => []];

        $this->common_data = \Request::get('common_data');
        $whois = $this->common_data['whois'];

        $this->pay_client = $this->initiatePaymentAPI($this->common_data['whois']->user->UserID);

        $language = $this->get_language_by_id($whois->UserLanguageId);

        if ($whois->UserLanguageId) {
            $language = $this->get_language_by_id($whois->UserLanguageId);
        }

        if ($language->Alpha2Code) {
            $common_translations = $this->get_translations_by_path("Common/System", $language->Alpha2Code);
            $error_translations = $this->get_translations_by_path("Global/Errors", $language->Alpha2Code);
        }

        $input = $request->all();

        if ($input['accountId']) {
            $input = ['accountId' => $input['accountId'], 'amount' => $input['amount']];
        }
        $input['attributes']['channelId'] = $whois->device_type;

        $iq_response = $this->make_neteller_deposit_iq($input);

        if ($iq_response->success) {


            return response()->json(['success' => 1, 'redirect' => '/', 'message' => $common_translations['Common_Neteller_Success_Deposit']->Translation]);

        } else {

            foreach ($iq_response->errors as $error) {
                $response['errors'][] = $error->msg;
            }

        }

        return Response::json($response);
        //pre($iq_response);

    }

    public function ajax_cc_withdraw(Request $request)
    {


        $this->client = $this->initiateRestAPI();

        $response = ['errors' => []];

        $this->common_data = \Request::get('common_data');
        $whois = $this->common_data['whois'];

        $this->pay_client = $this->initiatePaymentAPI($this->common_data['whois']->user->UserID);

        $language = $this->get_language_by_id($whois->UserLanguageId);

        if ($whois->UserLanguageId) {
            $language = $this->get_language_by_id($whois->UserLanguageId);
        }

        if ($language->Alpha2Code) {
            $common_translations = $this->get_translations_by_path("Common/System", $language->Alpha2Code);
            $error_translations = $this->get_translations_by_path("Global/Errors", $language->Alpha2Code);
        }

        $input = $request->all();
        unset($input['cardNumber']);
        $input['encCreditcardNumber'] = urldecode($input['encCreditcardNumber']);

        $iq_response = $this->make_cc_withdrawal_iq($input);

        if ($iq_response->success) {

            return response()->json(['success' => 1, 'redirect' => '/', 'message' => $common_translations['Common_CC_Success_Withdraw']->Translation]);

        } else {

            foreach ($iq_response->errors as $error) {
                $response['errors'][] = $error->msg;
            }

        }

        return Response::json($response);

    }


    public function ajax_common_withdraw(Request $request)
    {


        $this->client = $this->initiateRestAPI();

        $response = ['errors' => []];

        $this->common_data = \Request::get('common_data');
        $whois = $this->common_data['whois'];

        $this->pay_client = $this->initiatePaymentAPI($this->common_data['whois']->user->UserID);

        $language = $this->get_language_by_id($whois->UserLanguageId);

        if ($whois->UserLanguageId) {
            $language = $this->get_language_by_id($whois->UserLanguageId);
        }

        if ($language->Alpha2Code) {
            $common_translations = $this->get_translations_by_path("Common/System", $language->Alpha2Code);
            $error_translations = $this->get_translations_by_path("Global/Errors", $language->Alpha2Code);
        }

        $input = $request->all();

        $input['attributes']['channelId'] = $whois->device_type;

        if (isset($whois->user->bonus_history->UserBonuses)) {
            foreach ($whois->user->bonus_history->UserBonuses as $part) {
                $res = $this->delete_user_bonus_by_id($part->UserBonusId);
            }
        }


        if ($this->common_data['whois']->user->wallet->Balance < $input['amount']) {

            $response['errors'][] = $common_translations['Common_System_AmountExceed']->Translation;
            return Response::json($response);

        }


        $success_message = "";

        switch ($input['payment_type']) {
            case "neteller":
                $iq_response = $this->make_neteller_withdrawal_iq($input);
                $success_message = $common_translations['Common_Neteller_Success_Withdraw']->Translation;
                break;
            case "skrill":
                $iq_response = $this->make_skrill_withdrawal_iq($input);
                $success_message = $common_translations['Common_Skrill_Success_Withdraw']->Translation;
                break;
            case "trustly":
                $iq_response = $this->make_trustly_withdrawal_iq($input);
                break;
            case "ecopayz":
                $iq_response = $this->make_ecopayz_witdhrawal_iq($input);
                $success_message = $common_translations['Common_System_SuccessWD']->Translation;
                break;
            case "mastercard":
            case "visa":
            case "creditcard":
                $iq_response = $this->make_cc_withdrawal_iq($input);
                $success_message = $common_translations['Common_CC_Success_Withdraw']->Translation;
                break;
            case "banklocal":
            case "bdw":
                $iq_response = $this->make_banklocal_withdraw_iq($input);
                $success_message = $common_translations['Common_System_SuccessWD']->Translation;
                break;
            case "bankiban":
            case "bdiw":
                $iq_response = $this->make_bankiban_withdraw_iq($input);
                $success_message = $common_translations['Common_System_SuccessWD']->Translation;
                break;
            case "bankintl":
            case "bisw":
                $iq_response = $this->make_bankintl_withdraw_iq($input);
                $success_message = $common_translations['Common_System_SuccessWD']->Translation;
                break;
            default:
                return;
                break;
        }

        if ($iq_response->success) {

            if (isset($iq_response->redirectOutput)) {
                return response()->json(['output' => $iq_response->redirectOutput]);
            } else {
                return response()->json(['success' => 1, 'redirect' => '/', 'message' => $success_message]);
            }

        } else {

            foreach ($iq_response->errors as $error) {
                $response['errors'][] = $error->msg;
            }

        }

        return Response::json($response);

    }

    public function ajax_skrill_withdraw(Request $request)
    {

        $this->client = $this->initiateRestAPI();

        $response = ['errors' => []];

        $this->common_data = \Request::get('common_data');
        $whois = $this->common_data['whois'];

        $this->pay_client = $this->initiatePaymentAPI($this->common_data['whois']->user->UserID);

        $language = $this->get_language_by_id($whois->UserLanguageId);

        if ($whois->UserLanguageId) {
            $language = $this->get_language_by_id($whois->UserLanguageId);
        }

        if ($language->Alpha2Code) {
            $common_translations = $this->get_translations_by_path("Common/System", $language->Alpha2Code);
            $error_translations = $this->get_translations_by_path("Global/Errors", $language->Alpha2Code);
        }

        $input = $request->all();

        $iq_response = $this->make_skrill_withdrawal_iq($input);

        if ($iq_response->success) {

            return response()->json(['success' => 1, 'redirect' => '/', 'message' => $common_translations['Common_Skrill_Success_Withdraw']->Translation]);

        } else {

            foreach ($iq_response->errors as $error) {
                $response['errors'][] = $error->msg;
            }

        }

        return Response::json($response);

    }


    public function ajax_neteller_withdraw(Request $request)
    {

        $this->client = $this->initiateRestAPI();

        $response = ['errors' => []];

        $this->common_data = \Request::get('common_data');
        $whois = $this->common_data['whois'];

        $this->pay_client = $this->initiatePaymentAPI($this->common_data['whois']->user->UserID);

        $language = $this->get_language_by_id($whois->UserLanguageId);

        if ($whois->UserLanguageId) {
            $language = $this->get_language_by_id($whois->UserLanguageId);
        }

        if ($language->Alpha2Code) {
            $common_translations = $this->get_translations_by_path("Common/System", $language->Alpha2Code);
            $error_translations = $this->get_translations_by_path("Global/Errors", $language->Alpha2Code);
        }

        $input = $request->all();

        $iq_response = $this->make_neteller_withdrawal_iq($input);

        if ($iq_response->success) {

            return response()->json(['success' => 1, 'redirect' => '/', 'message' => $common_translations['Common_Neteller_Success_Withdraw']->Translation]);

        } else {

            foreach ($iq_response->errors as $error) {
                $response['errors'][] = $error->msg;
            }

        }

        return Response::json($response);

    }


    public function ajax_play_practice(Request $request)
    {

        $this->client = $this->initiateRestAPI();

        $response = ['errors' => []];

        $this->common_data = \Request::get('common_data');
        $whois = $this->common_data['whois'];

        $language = $this->get_language_by_id($whois->UserLanguageId);

        $input = $request->all();

        $game_input = [
            "language" => $language->Alpha2Code,
            "UserIP" => $_SERVER['REMOTE_ADDR'],
            "UserAgent" => $_SERVER['HTTP_USER_AGENT'],
            "PlayForFun" => true,
            "VariantID" => $whois->device->Type,
        ];

        if ($_SERVER['REMOTE_ADDR'] == '34.253.190.35') {

            //pre($game_input);

        }

        $likes_count = $this->count_likes($input['game_id']);

        if ($likes_count < 700) {

            $likes_count = $this->count_templike($input['game_id']);

            if (!$likes_count) {
                $likes_count = rand(2000, 4000);
                $this->insert_templike($input['game_id'], $likes_count);

            }
        }

        $url = $this->get_game_url($game_input, $input['game_id']);
        $game_url_redirect = "";
        $game = $this->get_game_by_id($input['game_id']);

        if (isset($game->GameId)) {
            $game_url_redirect = strtolower(str_replace(" ", "_", $game->Name));
        }


        return response()->json(['game_url' => $url, 'success' => 1, 'likes_count' => $likes_count, 'game_url_redirect' => $game_url_redirect, 'language' => $language->Alpha2Code]);

    }

    public function ajax_play_game(Request $request)
    {

        $this->client = $this->initiateRestAPI();


        $response = ['errors' => []];

        $this->common_data = \Request::get('common_data');
        $whois = $this->common_data['whois'];
        $whois->user = $this->get_user();

        $language = $this->get_language_by_id($whois->UserLanguageId);

        if ($language->Alpha2Code) {
            $error_translations = $this->get_translations_by_path("Global/Errors", $language->Alpha2Code);
        }

        if (!$whois->user) {
            $response['errors'][] = $error_translations["Error_NOT_LOGGED_IN"]->Translation;
            return Response::json($response);
        }

        $input = $request->all();

        $game_input = [
            "language" => $language->Alpha2Code,
            "UserIP" => $_SERVER['REMOTE_ADDR'],
            "UserAgent" => $_SERVER['HTTP_USER_AGENT'],
            "PlayForFun" => false,
            "VariantID" => $whois->device->Type,
        ];

        $likes_count = $this->count_likes($input['game_id']);

        if ($likes_count < 700) {

            $likes_count = $this->count_templike($input['game_id']);

            if (!$likes_count) {
                $likes_count = rand(2000, 4000);
                $this->insert_templike($input['game_id'], $likes_count);

            }
        }

        $url = $this->get_game_url($game_input, $input['game_id']);
        $game = $this->get_game_by_id($input['game_id']);

        if (isset($game->GameId)) {
            $game_url_redirect = strtolower(str_replace(" ", "_", $game->Name));
        }

        return response()->json(['game_url' => $url, 'success' => 1, 'likes_count' => $likes_count, 'game' => $game, 'game_url_redirect' => $game_url_redirect, 'language' => $language->Alpha2Code]);

    }

    public function ajax_reality_set(Request $request)
    {

        $input = $request->all();

        $this->client = $this->initiateRestAPI();

        $output = $this->save_reality_check_interval($input['reality_val']);


    }

    public function ajax_get_transactions(Request $request)
    {

        $this->client = $this->initiateRestAPI();

        $this->common_data = \Request::get('common_data');
        $whois = $this->common_data['whois'];

        $language = $this->get_language_by_id($whois->UserLanguageId);

        $response = ['errors' => []];

        if ($language->Alpha2Code) {
            $common_translations = $this->get_translations_by_path("Common/System", $language->Alpha2Code);
            $error_translations = $this->get_translations_by_path("Global/Errors", $language->Alpha2Code);
        }

        $input = $request->all();

        if (!$input['date']['from'] || !$input['date']['to']) {

            $period = $this->prepare_date_array(['action' => 'last']);
            $transactions = $this->get_wallet_transactions($period);

        } else {

            $action = "period";

            $date_diff = strtotime($input['date']['to']) - strtotime($input['date']['from']);
            if ($date_diff > 3888000) $action = "splitperiod";

            $period = $this->prepare_date_array(['action' => $action, 'from' => $input['date']['from'], 'to' => $input['date']['to']]);

            if ($action == "splitperiod") {

                $tmp_transactions = [];
                $transactions = null;
                foreach ($period['from'] as $key => $value) {
                    $tmp_transactions = $this->get_wallet_transactions(['from' => $period['from'][$key], 'to' => $period['to'][$key]]);
                    if ($tmp_transactions->Success) {
                        $transactions = $this->merge_transactions($transactions, $tmp_transactions);
                    } else {
                        $transactions = $tmp_transactions;
                        break;
                    }
                }

            } else {
                $transactions = $this->get_wallet_transactions($period);
            }

        }


        if ($_SERVER['REMOTE_ADDR'] == '91.189.128.27') {
            //pre($transactions);
        }

        if ($transactions->Success) {

            $view = \View::make('ajax_transactions', ['transactions' => $transactions, 'errors' => $response['errors'], 'common_data' => $this->common_data]);
            $content = $view->render();


        } else {

            foreach ($transactions->Errors as $error) {

                $response['errors'][] = $error_translations["Error_" . $error->Error]->Translation;

            }

            $view = \View::make('ajax_transactions', ['transactions' => $transactions, 'errors' => $response['errors'], 'common_data' => $this->common_data]);
            $content = $view->render();

        }

        $response = ['content' => $content, 'success' => 1];

        return Response::json($response);

    }


    public function ajax_get_game_transactions(Request $request)
    {

        $this->client = $this->initiateRestAPI();

        $this->common_data = \Request::get('common_data');
        $whois = $this->common_data['whois'];

        $language = $this->get_language_by_id($whois->UserLanguageId);

        $response = ['errors' => []];

        if ($language->Alpha2Code) {
            $common_translations = $this->get_translations_by_path("Common/System", $language->Alpha2Code);
            $error_translations = $this->get_translations_by_path("Global/Errors", $language->Alpha2Code);
        }

        $input = $request->all();

        if (!$input['date']['from'] || !$input['date']['to']) {

            $period = $this->prepare_date_array(['action' => 'last']);
            $transactions = $this->get_game_transactions($period);

        } else {

            $action = "period";

            $date_diff = strtotime($input['date']['to']) - strtotime($input['date']['from']);
            if ($date_diff > 3888000) $action = "splitperiod";

            $period = $this->prepare_date_array(['action' => $action, 'from' => $input['date']['from'], 'to' => $input['date']['to']]);

            if ($action == "splitperiod") {

                $tmp_transactions = [];
                $transactions = null;
                foreach ($period['from'] as $key => $value) {
                    $tmp_transactions = $this->get_game_transactions(['from' => $period['from'][$key], 'to' => $period['to'][$key]]);
                    if ($tmp_transactions->Success) {
                        $transactions = $this->merge_transactions($transactions, $tmp_transactions);
                    } else {
                        $transactions = $tmp_transactions;
                        break;
                    }
                }

            } else {
                $transactions = $this->get_game_transactions($period);
            }

        }

        if ($transactions->Success) {

            $view = \View::make('ajax_game_transactions', ['transactions' => $transactions, 'errors' => $response['errors'], 'common_data' => $this->common_data]);
            $content = $view->render();


        } else {

            foreach ($transactions->Errors as $error) {
                $response['errors'][] = $error_translations["Error_" . $error->Error]->Translation;
            }

            $view = \View::make('ajax_game_transactions', ['transactions' => $transactions, 'errors' => $response['errors'], 'common_data' => $this->common_data]);
            $content = $view->render();

        }

        $response = ['content' => $content, 'success' => 1];

        return Response::json($response);

    }

    public function ajax_online()
    {

        $this->client = $this->initiateRestAPI();

        $result = $this->is_logged_in();

        $response = ['result' => $result, 'success' => 1];

        return Response::json($response);

    }

    public function ajax_update_gdpr()
    {

        $this->client = $this->initiateRestAPI();

        $this->common_data = \Request::get('common_data');
        $whois = $this->common_data['whois'];

        $language = $this->get_language_by_id($whois->UserLanguageId);

        $all_consent = $this->get_all_consents($language->Alpha2Code);
        $consent_mandatories = $this->get_mandatory_consents_fields($all_consent);

        $prepared = $this->get_consents_to_send($consent_mandatories, $all_consent);

        $output = $this->save_user_consents_classic($prepared);

        $response = ['success' => 1];

        return Response::json($response);

    }

    public function ajax_forfeit_bonus(Request $request)
    {

        $this->client = $this->initiateRestAPI();

        $this->common_data = \Request::get('common_data');
        $whois = $this->common_data['whois'];

        $language = $this->get_language_by_id($whois->UserLanguageId);

        if ($language->Alpha2Code) {
            $common_translations = $this->get_translations_by_path("Common/System", $language->Alpha2Code);
        }

        $input = $request->all();

        $iq_response = $this->delete_user_bonus_by_id($input['bonus_id']);

        $response = ['success' => 1, 'message' => $common_translations["Common_System_ForfeitedBonus"]->Translation];

        return Response::json($response);

    }

    public function ajax_check_kyc_block()
    {

        $this->client = $this->initiateRestAPI();

        $this->common_data = \Request::get('common_data');

        $response = ['result' => $this->common_data['kyc_block']['kyc_block_id'], 'success' => 1];

        return Response::json($response);

    }

    public function ajax_sowq_first(Request $request)
    {
        $this->client = $this->initiateRestAPI();

        $response = ['errors' => []];

        $this->common_data = \Request::get('common_data');
        $whois = $this->common_data['whois'];

        $language = $this->get_language_by_id($whois->UserLanguageId);

        if ($language->Alpha2Code) {
            $common_translations = $this->get_translations_by_path("Common/System", $language->Alpha2Code);
            $error_translations = $this->get_translations_by_path("Global/Errors", $language->Alpha2Code);
        }

        $input = $request->all();
        unset($input["_token"]);
        $input = $this->prepare_sowq_qa($input);

        $output = $this->post_kyc_approval_types($input);

        $kyc = $this->get_user_kyc($this->common_data['whois']->user->UserID);
        $active_kyc = $this->check_active_kyc($kyc);

        if ($output->Success) {

            $response['success'] = 1;
            if ($active_kyc) {
                $view = \View::make('trans_parts', ['common_data' => $this->common_data, 'code' => 'Common_System_AfterSOWQActiveKYC']);
                $content = $view->render();
                $response['message'] = $content;
            } else {
                $response['message'] = $common_translations["Common_System_KYCProcessing"]->Translation;
            }

        } else {

            foreach ($output->Errors as $error) {
                $response['errors'][] = $error->Message;
            }

        }
        if ($_SERVER['REMOTE_ADDR'] == '91.189.128.27') {
            //pre($output);
        }
        return Response::json($response);


    }

    public function ajax_recover_confirm(Request $request)
    {

        $this->client = $this->initiateRestAPI();

        $response = ['errors' => []];

        $this->common_data = \Request::get('common_data');
        $whois = $this->common_data['whois'];

        $language = $this->get_language_by_id($whois->UserLanguageId);

        if ($language->Alpha2Code) {
            $common_translations = $this->get_translations_by_path("Common/System", $language->Alpha2Code);
            $error_translations = $this->get_translations_by_path("Global/Errors", $language->Alpha2Code);
        }

        $input = $request->all();

        $output = $this->send_forgotten_request($input);

        if ($output->Success) {

            $response['success'] = 1;
            $response['message'] = $common_translations["User_Recover_StepFirst_Success"]->Translation;

        } else {
            foreach ($output->Errors as $error) {

                if ($error->Error = "USER_NOT_FOUND") {
                    $error->Error = "RECOVER_USER_NOT_FOUND";
                }

                $response['errors'][] = $error_translations["Error_" . $error->Error]->Translation;
            }

        }

        return Response::json($response);

    }


    public function ajax_profile_modify(Request $request)
    {

        $this->client = $this->initiateRestAPI();

        $this->common_data = \Request::get('common_data');
        $whois = $this->common_data['whois'];

        if ($whois->IsExcluded) return redirect('/');

        $language = $this->get_language_by_id($whois->UserLanguageId);

        if ($language->Alpha2Code) {
            $error_translations = $this->get_translations_by_path("Global/Errors", $language->Alpha2Code);
            $common_translations = $this->get_translations_by_path("Common/System", $language->Alpha2Code);
        }

        $input = $request->all();

        $messages = [
            'required' => $error_translations['Error_FIELD_REQUIRED']->Translation,
            'alpha' => $error_translations['Error_FIELD_ALPHA']->Translation,
            'string' => $error_translations['Error_FIELD_STRING']->Translation,
            'integer' => $error_translations['Error_FIELD_INTEGER']->Translation,
        ];


        $validator = Validator::make($request->all(), [
            'address1' => 'required|string',
            'address2' => 'string|nullable',
            'postalcode' => 'required|string',
            'city' => 'required|string',
            'mobileprefix' => 'required|string',
            'mobile' => 'required|string',
            'languageid' => 'integer',
        ], $messages);

        if ($validator->passes()) {

            //pre($input);

            $all_consent = $this->get_all_consents($language->Alpha2Code);
            $consent_to_send = $this->get_consents_to_send($input, $all_consent);

            $this->save_user_consents($consent_to_send, $this->common_data['whois']->user->UserID);

            $igc_response = $this->do_user_update($input);

            if ($igc_response->Success) {

                $response['success'] = 1;
                $response['message'] = $common_translations["User_Profile_Updated"]->Translation;

                //$result = $this->send_verification_sms($input);

                return Response::json($response);

            } else {

                foreach ($igc_response->Errors as $error) {
                    $validator->errors()->add('field', $error_translations['Error_' . $error->Error]->Translation);
                }

            }

        }
        return response()->json(['errors' => $validator->errors()->all()]);

    }

    public function ajax_change_email(Request $request)
    {

        $this->client = $this->initiateRestAPI();

        $this->common_data = \Request::get('common_data');
        $whois = $this->common_data['whois'];

        $language = $this->get_language_by_id($whois->UserLanguageId);

        if ($language->Alpha2Code) {
            $error_translations = $this->get_translations_by_path("Global/Errors", $language->Alpha2Code);
            $common_translations = $this->get_translations_by_path("Common/System", $language->Alpha2Code);
        }

        $input = $request->all();

        $messages = [
            'email' => $error_translations['Error_FIELD_EMAIL']->Translation,
            'required' => $error_translations['Error_FIELD_REQUIRED']->Translation,
        ];


        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ], $messages);

        if ($validator->passes()) {

            $igc_response = $this->change_user_email($input);

            if ($igc_response->Success) {

                $response['success'] = 1;
                $response['redirect'] = "/logout";

                return Response::json($response);

            } else {

                foreach ($igc_response->Errors as $error) {
                    $validator->errors()->add('field', $error_translations['Error_' . $error->Error]->Translation);
                }

            }

        }

        return response()->json(['errors' => $validator->errors()->all()]);

    }

    public function ajax_change_password(Request $request)
    {

        $this->client = $this->initiateRestAPI();

        $this->common_data = \Request::get('common_data');
        $whois = $this->common_data['whois'];

        $language = $this->get_language_by_id($whois->UserLanguageId);

        if ($language->Alpha2Code) {
            $error_translations = $this->get_translations_by_path("Global/Errors", $language->Alpha2Code);
            $common_translations = $this->get_translations_by_path("Common/System", $language->Alpha2Code);
        }

        $input = $request->all();

        $messages = [
            'required' => $error_translations['Error_FIELD_REQUIRED']->Translation,
            'password.regex' => $error_translations['Error_FIELD_PASSWORD_FORMAT']->Translation,
            'same' => $error_translations['Error_FIELD_SAME']->Translation,
        ];


        $validator = Validator::make($request->all(), [
            'newpassword' => 'required|between:6,25|regex:/^.*(?=.{3,})(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9]).*$/',
            'newpassword_confirm' => 'same:newpassword',
            'oldpassword' => 'required',
        ], $messages);

        if ($validator->passes()) {

            $igc_response = $this->change_user_password($input);

            if ($igc_response->Success) {

                $response['success'] = 1;
                $response['redirect'] = "/logout";

                return Response::json($response);

            } else {

                foreach ($igc_response->Errors as $error) {
                    $validator->errors()->add('field', $error_translations['Error_' . $error->Error]->Translation);
                }

            }

        }

        return response()->json(['errors' => $validator->errors()->all()]);

    }


    public function ajax_contact_support(Request $request)
    {

        $this->client = $this->initiateRestAPI();

        $this->common_data = \Request::get('common_data');
        $whois = $this->common_data['whois'];

        if ($whois->IsExcluded) return redirect('/');

        $language = $this->get_language_by_id($whois->UserLanguageId);

        if ($language->Alpha2Code) {
            $error_translations = $this->get_translations_by_path("Global/Errors", $language->Alpha2Code);
            $common_translations = $this->get_translations_by_path("Common/System", $language->Alpha2Code);
        }


        $messages = [
            'required' => $error_translations['Error_FIELD_REQUIRED']->Translation,
            'email' => $error_translations['Error_FIELD_EMAIL']->Translation,
            'alpha' => $error_translations['Error_FIELD_ALPHA']->Translation,
            'string' => $error_translations['Error_FIELD_STRING']->Translation,
        ];

        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'firstname' => 'required|alpha',
            'topic' => 'required',
            'subject' => 'required|string',
            'comment' => 'required|string',
        ], $messages);

        if ($validator->passes()) {

            $input = $request->all();

            $to = 'support@justcasino.com';

            $subject = 'JustCasino Support Form';

            $headers = "From: admin@justcasino.com\r\n";
            $headers .= "Reply-To: " . strip_tags($input['email']) . "\r\n";
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html;charset=utf-8\r\n";

            $view = \View::make('emails.support', ['input' => $input]);
            $contents = $view->render();

            mail($to, $subject, $contents, $headers);

            return response()->json(['success' => 1, 'message' => $common_translations['User_Contact_Support_Success']->Translation]);

        }

        return response()->json(['errors' => $validator->errors()->all()]);


    }

    public function blank()
    {
    }

    public function ajax_check_wallet(Request $request)
    {

        $this->client = $this->initiateRestAPI();

        $input = $request->all();

        $response = ['errors' => []];

        $this->common_data = \Request::get('common_data');

        $wallet = [
            'total_money' => number_format($this->common_data['whois']->user->wallet->total_money, 2),
            'bonus_money' => number_format($this->common_data['whois']->user->wallet->real_bonus, 2),
            'locked_money' => number_format($this->common_data['whois']->user->wallet->locked_money, 2),
            'withdraw_money' => number_format($this->common_data['whois']->user->wallet->Balance, 2),
            'real_money' => number_format($this->common_data['whois']->user->wallet->total_money - $this->common_data['whois']->user->wallet->real_bonus, 2),
        ];

        return response()->json(['wallet' => $wallet]);

    }


    public function ajax_phone_verify(Request $request)
    {

        $this->client = $this->initiateRestAPI();

        $response = ['errors' => []];

        $this->common_data = \Request::get('common_data');
        $whois = $this->common_data['whois'];

        $language = $this->get_language_by_id($whois->UserLanguageId);

        if ($language->Alpha2Code) {
            $common_translations = $this->get_translations_by_path("Common/System", $language->Alpha2Code);
            $error_translations = $this->get_translations_by_path("Global/Errors", $language->Alpha2Code);
        }

        if ($whois->IsExcluded) $response['errors'][] = $common_translations['User_Registration_Forbidden']->Translation;

        $input = $request->all();

        $output = $this->verify_by_sms($input);

        if ($output->Data && count($response['errors']) == 0) {

            return response()->json(['success' => 1, 'message' => $common_translations['User_Registration_FirstStep_Success']->Translation]);

        } else {

            foreach ($output->Errors as $error) {

                $response['errors'][] = $error_translations["Error_" . $error->Error]->Translation;
            }

        }

        return Response::json($response);

    }

    public function ajax_cancel_withdrawal(Request $request)
    {

        $this->client = $this->initiateRestAPI();

        $response = ['errors' => []];

        $this->common_data = \Request::get('common_data');
        $whois = $this->common_data['whois'];

        $language = $this->get_language_by_id($whois->UserLanguageId);

        if ($language->Alpha2Code) {
            $common_translations = $this->get_translations_by_path("Common/System", $language->Alpha2Code);
            $error_translations = $this->get_translations_by_path("Global/Errors", $language->Alpha2Code);
        }

        if ($whois->IsExcluded) $response['errors'][] = $common_translations['User_Registration_Forbidden']->Translation;

        $input = $request->all();


        $igc_response = $this->cancel_withdrawal_transaction($input['txId']);

        if ($igc_response->Success) {

            return response()->json(['success' => 1]);

        } else {

            foreach ($igc_response->Errors as $error) {
                $response['errors'][] = $error->Message;
            }

        }

        return Response::json($response);

    }

    public function ajax_get_games(Request $request)
    {

        $input = $request->all();

        $file = file_get_contents($_SERVER['DOCUMENT_ROOT'] . '../storage/app/public/games-en.json');
        $response = json_decode($file);

        return Response::json($response);


    }

    public function ajax_favorite_game(Request $request)
    {

        $this->client = $this->initiateRestAPI();

        $input = $request->all();

        $response = ['errors' => []];

        $this->common_data = \Request::get('common_data');
        $whois = $this->common_data['whois'];

        $language = $this->get_language_by_id($whois->UserLanguageId);

        if ($language->Alpha2Code) {
            $error_translations = $this->get_translations_by_path("Global/Errors", $language->Alpha2Code);
        }

        if (!$this->is_logged_in()) {

            $response['errors'][] = $error_translations["Error_NOT_LOGGED_IN"]->Translation;

        } else {

            if ($input['onoff'] > -1) {
                $output = $this->add_favorite_game($input['game_id']);
            } else {
                $output = $this->remove_favorite_game($input['game_id']);
            }

            return response()->json(['success' => 1]);

        }

        return Response::json($response);

    }

    public function ajax_like_game(Request $request)
    {

        $this->client = $this->initiateRestAPI();

        $input = $request->all();

        $response = ['errors' => []];

        $whois = $this->whois(\Request::ip());
        $this->common_data = \Request::get('common_data');

        $language = $this->get_language_by_id($whois->UserLanguageId);

        if ($language->Alpha2Code) {
            $error_translations = $this->get_translations_by_path("Global/Errors", $language->Alpha2Code);
        }

        if (!$this->is_logged_in()) {

            $response['errors'][] = $error_translations["Error_NOT_LOGGED_IN"]->Translation;

        } else {

            if ($input['onoff'] > -1) {
                $output = $this->like_game($input['game_id'], $this->common_data['whois']->user->UserID);
            } else {
                $output = $this->dislike_game($input['game_id'], $this->common_data['whois']->user->UserID);
            }

            $count = $this->count_likes($input['game_id']);

            return response()->json(['success' => 1, 'count' => $count]);

        }

        return Response::json($response);

    }


    public function ajax_upload_kyc(Request $request)
    {

        $this->client = $this->initiateRestAPI();

        $input = $request->all();
        //pre($input);
        //exit;


        $response = ['errors' => []];

        $whois = $this->whois(\Request::ip());
        $this->common_data = \Request::get('common_data');

        $language = $this->get_language_by_id($whois->UserLanguageId);

        if ($language->Alpha2Code) {
            $error_translations = $this->get_translations_by_path("Global/Errors", $language->Alpha2Code);
            $common_translations = $this->get_translations_by_path("Common/System", $language->Alpha2Code);
        }

        $messages = [
            'required' => $error_translations['Error_FIELD_REQUIRED']->Translation,
        ];

        $validator = Validator::make($request->all(), [
            'kyc' => 'required',
        ], $messages);

        if ($validator->passes()) {

            foreach ($input['kyc'] as $key => $value) {

                $igc_input = [
                    'data' => base64_encode(file_get_contents($value->path())),
                    'KYCID' => $key,
                    'extension' => $value->extension()
                ];

                if (isset($input['docType'])) {
                    $igc_input['docType'] = $input['docType'];
                }

                if (isset($input['docNo'])) {
                    $igc_input['docNo'] = $input['docNo'];
                }

                if (isset($input['docExpiryDate'])) {
                    $igc_input['docExpiryDate'] = $input['docExpiryDate'];
                }

                $igc_response = $this->upload_user_kyc($igc_input);

                if ($_SERVER['REMOTE_ADDR'] == '91.189.128.27') {
                    //pre($igc_response);
                }

            }

            if ($igc_response->Success) {

                $this->common_data['kyc'] = $this->get_user_kyc($this->common_data['whois']->user->UserID);
                $this->common_data['kyc_status'] = $this->get_kyc_status();
                $view = \View::make('kyc_grid', ['common_data' => $this->common_data]);
                $content = $view->render();

                return response()->json(['success' => 1, 'content' => $content]);

            } else {

                foreach ($igc_response->Errors as $error) {
                    $validator->errors()->add('field', $error_translations['Error_' . $error->Error]->Translation);
                }

            }


        }

        if ($_SERVER['REMOTE_ADDR'] == '91.189.128.27' || $_SERVER['REMOTE_ADDR'] == '34.253.190.37') {
            //pre($input);
            //exit;
        }


        return response()->json(['errors' => $validator->errors()->all()]);

    }

    public function ajax_registration(Request $request)
    {

        $input = $request->all();


        $field_rule = [
            'email' => 'required|email|unique:players',
            'password' => 'required|between:3,25|regex:/^(?=\S+$)/',
            'condition' => 'required|boolean|min:1'
        ];

        $messages = [
            'condition.min' => 'Please, accept terms and conditions, click the checkbox',
            'password.between' => 'Password should contain 8 characters including numbers, upper and lower case letters',
            'password.regex' => 'Password should contain 8 characters including numbers, upper and lower case letters'
        ];

        $validator = Validator::make($input, $field_rule, $messages);

        if ($validator->passes()) {
            $input['useragent'] = $request->header('User-Agent');
            $input['affiliate_aid'] = $request->cookie('aid') ?? '96-78';
            $input['query_string'] = [];

            try {
                $info = get_ip_info($request->ip());
                $input['city'] = $info['city'];
                $input['country'] = $info['countryCode'];
                $input['register_ip'] = $request->ip();
                $country = $this->get_country_by_code($info['countryCode']);
                $input['country_id'] = $country->id;
            } catch (ErrorException $exception) {
                $input['city'] = '';
                $input['country'] = '';
            }

            try {
                $mail = new RealEmail();

                $email = $input['email'];

                if($mail->check($email)){
                    $input['mail_real'] = 1;
                }elseif(RealEmail::validate($email)){
                    $input['mail_real'] = 0;
                }else{
                    $input['mail_real'] = 0;
                }

            } catch (ErrorException $exception) {
                $input['mail_real'] = 0;
            }

            $new_player = $this->create_player($input);

            if ($new_player->id) {


                $this->create_wallet($new_player->id);
                Auth::guard('clients')->loginUsingId($new_player->id, true);
                $user = Auth::guard('clients')->user();
                $wallets = $this->get_user_wallets($new_player->id);
                $new_player->verifyPlayer()->create(['token' => Str::random(60)]);
//                Mail::to($new_player->email)->send(new \App\Mail\VerifyPlayer($new_player));

                $this->send_email('verify_player', $new_player->email, ['email' => $new_player->email,  'token' => $new_player->verifyPlayer->token]);

                $agent = new Agent();
                $view_type = $agent->isDesktop() ? 1 : 2;

                if ($input['affiliate_aid']) {
//                    $this->prepare_callback(Config::get('app.affiliates_url'), ['json' => ['aid' => $new_player->affiliate_aid, 'player_id' => $new_player->id, 'ip' => \Request::ip(), 'email' => $new_player->email]]);
//                    $this->open_callback();
                    if (!empty($request->cookie('query_string'))) {
                        $input['query_string'] = json_decode($request->cookie('query_string'));
                    }

                    $response = Http::post('https://affiliates.bitfiring.com/register_open', [
                        'aid' => $new_player->affiliate_aid,
                        'player_id' => $new_player->id,
                        'ip' => $request->ip(),
                        'email' => $new_player->email,
                        'query_string' => $input['query_string'] ?? '',
                        'country_id' => $input['country_id']
                    ]);

                    $response = $response->json();

                    $partner = $response['data'];
                    $partner = Partners::updateOrCreate([
                        'partner_id' => $partner['id'],
                        'email' => $partner['email'],
                        'fullname' => $partner['company']
                    ]);

                    $new_player->partner_id = $partner->id;
                    $new_player->save();
                }


                $this->insert_session([
                    "user_id" => $user->id,
                    "user_agent" => $request->server('HTTP_USER_AGENT'),
                    "device_type" => $view_type,
                    "city" => $input['city'],
                    "country" => $input['country'],
                    "ip" => \Request::ip(),
                    "device" => $agent->device(),
                    "platform" => $agent->platform(),
                    "browser" => $agent->browser(),
                    "viewport" => $input['viewport']['w'] . 'x' . $input['viewport']['h']
                ]);

                if (Auth::guard('clients')->check()) {
                    $this->user_signed_in($user);

                    if (!empty($request->cookie('lucky'))) {
                        Http::post('https://bitfiring.back-dev.com/lucky/public/registration', ['user_id' => $new_player->id, 'session_id' => $request->cookie('lucky'), 'duplicated' => Auth::guard('clients')->user()->duplicated]);
                    }

                    if ($input['prize'] && !Auth::guard('clients')->user()->duplicated) {
                        $freespin = FreespinBonusModel::query()
                            ->where('id', '=', $input['prize'])
                            ->first();

                        $expiry = new \App\Models\Bonuses();
                        $expiry_method = $freespin->activate_duration_type ? $expiry::DURATION[$freespin->activate_duration_type] : null;


                        FreespinIssue::query()
                            ->insert([
                                'title' => $freespin->title,
                                'player_id' => $new_player->id,
                                'currency_id' => $freespin->currency_id,
                                'bonus_id' => $freespin->id,
                                'count' => $freespin->count,
                                'win' => 0,
                                'stage' => 2,
                                'status' => 1,
                                'active_until' => $expiry_method ? $expiry->$expiry_method($freespin->activate_duration) : null
                            ]);

                        $this->send_email('freespins_promo', $new_player->email, ['email' => $new_player->email,  'count' => $freespin->count]);
                    }
                }


                return response()->json(['success' => 1, 'user' => $user, 'wallets' => $wallets]);
            }

        }

        return response()->json(['errors' => $validator->errors()->all(), 'error_keys' => array_keys($validator->errors()->messages())]);
    }


    public function ajax_currency(Request $request)
    {
        $currency = $this->get_currency();

        return response()->json(['currency' => $currency]);
    }

    public function ajax_payment_system(Request $request)
    {
        $currency = $this->get_payment_system();

        return response()->json(['payment_system' => $currency]);
    }

    public function ajax_payment_currency_id(Request $request)
    {
        $this->common_data = \Request::get('common_data');

        if (isset($this->common_data['user']->id)) {

            $ps = \App\Models\Payments::query()->select('currency_id')
                ->where('user_id', '=', $this->common_data['user']->id)
                ->where('status', 1)
                ->where('type_id', 3)
                ->groupBy('currency_id')->pluck('currency_id');
            return response()->json(['success' => 1, 'payment_system' => $ps]);
        }
    }

    public function update_player(Request $request)
    {
        $input = $request->all();
        $dob = $input['dob'];
        $user = $input['user'];

        $current_player = Auth::guard('clients')->user();

        $field_rule = [
            'user.gender' => 'integer',
            'user.firstname' => 'required|string|max:15',
            'user.lastname' => 'required|string|max:15',
            'user.country_id' => 'integer',
            'user.city' => 'string|max:15',
            'user.address' => 'string|max:100|nullable',
            'user.postal_code' => 'max:15',
            'user.phone' => 'max:25',
            'dob.d' => 'integer|min:1|max:31|nullable',
            'dob.m' => 'integer|min:1|max:12|nullable',
            'dob.y' => 'integer|min:1900|max:2020|nullable',
        ];


        if ($user['email'] !== $current_player->email) {
            $field_rule = array_merge($field_rule, ['email' => 'required|email|unique:players']);
        }



        $validator = Validator::make($input, $field_rule);

        if (!empty($dob['d']) && empty($dob['m']) && empty($dob['y']) ||
            empty($dob['d']) && !empty($dob['m']) && empty($dob['y']) ||
            empty($dob['d']) && empty($dob['m']) && !empty($dob['y']) ||
            !empty($dob['d']) && !empty($dob['m']) && empty($dob['y']) ||
            empty($dob['d']) && !empty($dob['m']) && !empty($dob['y']) ||
            !empty($dob['d']) && empty($dob['m']) && !empty($dob['y'])
        ) {
            $validator->errors()->add('dob', 'incorrect dob');
        }

        if (count($validator->errors()->all()) == 0 && $validator->passes()) {
            $current_player->email = $user['email'];
            $current_player->gender = $user['gender'] ?? 0;
            $current_player->firstname = $user['firstname'];
            $current_player->lastname = $user['lastname'];
            $current_player->fullname = $user['firstname'] . ' ' . $user['lastname'];
            $current_player->country_id = $user['country_id'] ?? "";
            $current_player->city = $user['city'] ?? "Not provided";
            $current_player->address = $user['address'] ?? "";
            $current_player->postal_code = $user['postal_code'] ?? "";
            $current_player->phone = $user['phone'] ?? "";
            $current_player->promo_email = $user['promo_email'] ?? "";
            $current_player->promo_sms = $user['promo_sms'];
            if (!empty($dob['d']) && !empty($dob['m']) && !empty($dob['y'])) {
                $current_player->dob = Carbon::parse($dob['y'] . '-' . $dob['m'] . '-' . $dob['d'])->format('Y-m-d');
            }
            $current_player->save();
            return response()->json(['success' => 1, 'user' => $current_player]);
        }

        return response()->json(['errors' => $validator->errors()->all(), 'error_keys' => array_keys($validator->errors()->messages())]);
    }

    public function player_wallets(Request $request)
    {

        $this->common_data = \Request::get('common_data');

        if (isset($this->common_data['user']->id)) {
            $user_id = $this->common_data['user']->id;

            $wallets = $this->get_wallets($user_id);
            $ps = $this->get_ps();

            return response()->json(['wallets' => $wallets, 'ps' => $ps]);
        }

        if ($this->common_data['request_type'] == "ajax") {

            if (!isset($this->common_data['user']->id)) return response()->json(['redirect' => '/?session_expired=1']);

            $view = \View::make('parts/wallet', ['common_data' => $this->common_data]);
            $content = $view->render();
            return response()->json(['content' => $content]);

        }

        if (!isset($this->common_data['user']->id)) return redirect('/?session_expired=1');

        return view('wallet', ['tab' => 'wallet', 'common_data' => $this->common_data]);

    }

    public function set_primary_wallet(Request $request)
    {
        $this->common_data = \Request::get('common_data');
        $input = $request->all();

        $field_rule = [
            'id' => 'required|integer',
        ];

        $validator = Validator::make($input, $field_rule);
        if ($validator->passes()) {
            $this->update_wallet_primary($this->common_data['user']->id, $input['id']);
            return response()->json(['success' => 1]);
        }
        return response()->json(['error' => 'Something went wrong!']);
    }

    public function player_game_sessions(Request $request)
    {

        $this->common_data = \Request::get('common_data');
        $input = $request->all();

        if (isset($this->common_data['user']->id)) {

            $field_rule = [
                'page' => 'required|integer',
            ];

            $validator = Validator::make($input, $field_rule);
            if ($validator->passes()) {

                $game_session = $this->get_game_bet_session($this->common_data['user']->id, $input['page']);

                return response()->json($game_session);
            }
        }
    }

    public function set_new_user_wallet(Request $request)
    {
        $this->common_data = \Request::get('common_data');
        $input = $request->all();

        $input = array_filter($input, function ($v, $k) {
            if ($k === 'ipinfo') {
                return false;
            }
            return true;
        }, 1);


        $field_rule = [
            'currency_id' => 'required|integer',
        ];

        $validator = Validator::make($input, $field_rule);

        if ($validator->passes()) {
            $args = array_merge($input, ['user_id' => $this->common_data['user']->id]);
            $new_wallet_id = $this->new_user_wallet($args);
            if ($input['primary']) {
                $this->update_wallet_primary($this->common_data['user']->id, $new_wallet_id);
            }
            $wallets = $this->get_user_wallets($this->common_data['user']->id);
            return response()->json(['success' => 1, 'wallets' => $wallets]);
        }
        return response()->json(['error' => 'Something went wrong!']);
    }


    public function get_user_translation(Request $request)
    {
        $this->common_data = \Request::get('common_data');
        $input = $request->all();
        if (isset($this->common_data['user']->id)) {
            $input = array_filter($input, function ($v, $k) {
                if ($k === 'created_at') {
                    return !empty($v['start']) && !empty($v['end']);
                }
                return !empty($v) && $k !== 'ipinfo';
            }, 1);

            $params = [['transactions.player_id', '=', $this->common_data['user']->id]];
            $transactions = $this->get_payments($params, $input);

            return response()->json(['success' => 1, 'transactions' => $transactions]);
        }
    }

    public function get_bonuses(Request $request)
    {
        $this->common_data = \Request::get('common_data');
        if (isset($this->common_data['user']->id)) {

            $db = $this->set_bonuses($this->common_data['user']->id);

            return response()->json(['success' => 1, 'bonuses' => $db]);
        }
    }

    public function cancel_bonuses(Request $request)
    {
        $this->common_data = \Request::get('common_data');
        $input = $request->all();
        if (isset($this->common_data['user']->id)) {

            $db = $this->get_user_issued_bonus($this->common_data['user']->id, ['bonus_issue.id' => $input['bonus_id'], 'bonus_issue.status' => 2]);
            $user_bonus = $db->first();
            if (isset($user_bonus->id)) {
                if ($user_bonus->cat_type === 2) {
                    $amount = -1 * $user_bonus->locked_amount;
                } else {
                    $ggr = $user_bonus->locked_amount - $user_bonus->fixed_amount;

                    if($user_bonus->amount - $ggr > 0){
                        $amount = -($user_bonus->fixed_amount - ($user_bonus->amount - $ggr));
                    }

                    if($user_bonus->amount - $ggr < 0){
                        $amount = -$user_bonus->fixed_amount;
                    }

                    if($user_bonus->amount - $ggr >= $user_bonus->amount){
                        $amount = -($user_bonus->amount - $ggr);
                    }
                }

                $wallet = \App\Models\Wallets::query()
                    ->where('user_id', $this->common_data['user']->id)
                    ->where('currency_id', '=', $user_bonus->currency_id)
                    ->first();

                $this->insert_transaction([
                    'amount' => $amount,
                    'bonus_part' => 0,
                    'currency_id' => $user_bonus->currency_id,
                    'reference_id' => $user_bonus->id,
                    'wallet_id' => $wallet->id,
                    'player_id' => $this->common_data['user']->id,
                    'type_id' => 1,
                    'reference_type_id' => 5,
                    'amount_usd' => $amount
                ]);

                $this->handler_user_wallet($this->common_data['user']->id, $amount);
                $this->update_bonus($user_bonus, 5);

                event(new UpdateBalance($this->common_data['user']->id));
                return response()->json(['success' => 1, ['bonus_id' => $user_bonus->id]]);
            }


        }
    }

    public function active_bonuses(Request $request)
    {
        $this->common_data = \Request::get('common_data');
        $input = $request->all();
        if (isset($this->common_data['user']->id)) {

            $db = $this->get_user_issued_bonus($this->common_data['user']->id, ['bonus_issue.id' => $input['bonus_id'], 'bonus_issue.status' => 1]);
            $user_bonus = $db->first();
            if (isset($user_bonus->id)) {
                if ($user_bonus->admin_id) {
                    $amount = $user_bonus->fixed_amount;
                } else {
                    if ($user_bonus->fixed_amount <= $user_bonus->locked_amount) {
                        $amount = ($user_bonus->fixed_amount * ($user_bonus->amount / $user_bonus->locked_amount));
                    } else {
                        $dep = $user_bonus->fixed_amount - ($user_bonus->locked_amount - $user_bonus->amount);
                        $amount = $dep;
                    }
                }

                $currency = DB::table('currency')->where('id', $user_bonus->currency_id)->first();

                $wallet = DB::table('wallets')->where([
                    ['user_id', '=', $this->common_data['user']->id],
                    ['currency_id', '=', $currency->id]
                ])->first();

                $amount = $amount * (float)$currency->rate;


                if (empty($wallet)) {
                    $array = [[
                        'primary' => 1,
                        'currency_id' => $currency->id,
                        'user_id' => $this->common_data['user']->id,
                        'balance' => $amount,
                    ]];

                    DB::table('wallets')->insert($array);
                } else {
                    $new_balance = ((float)$wallet->balance ?? 0) + $amount;

                    DB::table('wallets')->where('id', '=', $wallet->id)->update([
                        'balance' => $new_balance,
                    ]);
                }

                $this->update_bonus($user_bonus, 2);
                event(new UpdateBalance($this->common_data['user']->id));
                return response()->json(['success' => 1, ['bonus_id' => $user_bonus->id]]);
            }
        }
    }

    public function get_user_freespins(Request $request)
    {
        $this->common_data = \Request::get('common_data');
        $input = $request->all();
        if (isset($this->common_data['user']->id)) {
            $db = DB::table('freespin_issue')
                ->leftJoin('freespin_bonus', 'freespin_issue.bonus_id', '=', 'freespin_bonus.id')
                ->where('freespin_issue.player_id', '=', $this->common_data['user']->id)
                ->where('freespin_issue.status', '=', 1)
                ->select('freespin_bonus.title as name', 'freespin_issue.created_at as issued', 'freespin_issue.active_until', 'freespin_issue.count', 'freespin_issue.status', 'freespin_issue.stage', 'freespin_issue.id')
                ->get();


            return response()->json(['success' => 1, 'freespins' => $db]);
        }
    }

    public function get_user_wallet()
    {
        $this->common_data = \Request::get('common_data');
        if (isset($this->common_data['user']->id)) {
            $wallets = $this->get_user_wallets($this->common_data['user']->id);
//            event(new UpdateWallets($this->common_data['user']->id, $wallets));
            return response()->json(['success' => 1, 'wallets' => $wallets]);
        }
    }

    public function get_user_bonuses_locked()
    {
        $this->common_data = \Request::get('common_data');
        if (isset($this->common_data['user']->id)) {
            $bonuses = $this->get_user_issued_bonus($this->common_data['user']->id, [["bonus_issue.status", "=", 2]]);
//            event(new UpdateWallets($this->common_data['user']->id, $wallets));
            return response()->json(['success' => 1, 'bonuses' => $bonuses]);
        }
    }

    public function ajax_countries()
    {
        $this->common_data = \Request::get('common_data');
        if (isset($this->common_data['user']->id)) {
            $countries = $this->get_countries();
            return response()->json(['success' => 1, 'countries' => $countries]);
        }
    }

    public function ajax_verify_player(Request $request)
    {
        $input = $request->all();

        $verify_player = VerifyPlayer::query()->where('token', '=', $input['token'])->first();

        if (!isset($verify_player->id)) {
            return response()->json(['error' => 1, 'errors' => 'Invalid Token']);
        }

        if ($verify_player->user->mail_verified) {
            return response()->json(['error' => 1, 'errors' => 'Player verified']);
        }
        $verify_player->user->mail_verified = 1;
        $verify_player->user->save();
        return response()->json(['success' => 1]);
    }

    public function affilate($id)
    {
        $new_player = Players::find($id);

        if ($new_player->affiliate_aid) {
            $session = $new_player->sessions->first();

//                    $this->prepare_callback(Config::get('app.affiliates_url'), ['json' => ['aid' => $new_player->affiliate_aid, 'player_id' => $new_player->id, 'ip' => \Request::ip(), 'email' => $new_player->email]]);
//                    $this->open_callback();
            return Http::post('https://affiliates.bitfiring.com/register_open', ['aid' => $new_player->affiliate_aid, 'player_id' => $new_player->id, 'ip' => $session->ip, 'email' => $new_player->email])->throw(function ($response, $e) {
                //
            })->json();
        }

        dd(false);
    }

    public function dep($id)
    {
        //589
        $payment = Payments::find($id);
        $new_player = Players::find($payment->user_id);

        $bonus = BonusIssue::find(142);

        // if (this.aid) {
        //     axios.post(process.env.AFFILATES_APP_URL + 'register_deposit', {
        //         aid: this.aid,
        //         player_id: this.user,
        //         amount: this.usd,
        //         amount_cents: (this.usd * 100),
        //         deposit_id: this.payment_id,
        //         bonus: this.bonus
        //     }, {
        //         headers: {
        //             'Content-Type': 'application/json'
        //         }
        //     }).then(response => {
        //         console.log(response.data)
        //     }).catch(error => {
        //         console.log(error)
        //     })
        // }

        if ($new_player->affiliate_aid) {
            $amount = 102.20;
//                    $this->prepare_callback(Config::get('app.affiliates_url'), ['json' => ['aid' => $new_player->affiliate_aid, 'player_id' => $new_player->id, 'ip' => \Request::ip(), 'email' => $new_player->email]]);
//                    $this->open_callback();
            return Http::post('https://affiliates.bitfiring.com/register_deposit', [
                'aid' => $new_player->affiliate_aid,
                'player_id' => $payment->user_id,
                'amount' => $amount,
                'amount_cents' => $amount * 100,
                'deposit_id' => $payment->id,
                'bonus' => $bonus->toArray()
            ])->throw(function ($response, $e) {
                //
            })->json();
        }

        dd(false);
    }

    public function rule_email(){
        $mail = new RealEmail();

        $mail->setStreamTimeoutWait(150);

        $mail->Debug= TRUE;
        $mail->Debugoutput= 'echo';

        $email = 'bitfiring@gmail.com';

        if($mail->check($email)){
            dd('Email &lt;'.$email.'&gt; is exist!');
        }elseif(RealEmail::validate($email)){
            dd('Email &lt;'.$email.'&gt; is valid, but not exist!');
        }else{
            dd('Email &lt;'.$email.'&gt; is not valid and not exist!');
        }
    }

}
