<?php

namespace App\Http\Traits;

use App\Events\UpdateBalance;
use App\Events\UpdateWallets;
use App\Models\FreespinBonusModel;
use App\Models\Wallets;
use App\Models\WalletsTemp;
use Illuminate\Support\Facades\Http;
use Carbon\Exceptions\InvalidFormatException;
use DB;
use Eloquent;
use Carbon\Carbon;

trait DBTrait
{

    public function get_request_duplicate($id)
    {

        return DB::table('provider_request_checker')
            ->select()
            ->where('request_id', '=', $id)
            ->get()->first();
    }

	public function insert_request_duplicate($request_id) {

        $array = [
            'request_id' => $request_id,
        ];

        DB::table('provider_request_checker')->insert($array);

        return DB::getPdo()->lastInsertId();

	}


    public function get_static_by_code($url_code, $locale = 'en')
    {

        $lang = \App\Models\Languages::query()->where('code', $locale)->first();

       $page = DB::table('pages')
            ->select(['pages.id',
                'page_translations.title',
                'page_translations.description',
                'page_translations.meta_description',
                'page_translations.headline'])
            ->join('page_translations', 'pages.id', '=', 'page_translations.page_id')
            ->where('code', '=', $url_code)
            ->where('page_translations.language_id', '=', $lang->id ?? 1)
            ->first();

       if(!isset($page->id)){
           $page = DB::table('pages')
               ->select(['pages.id',
                   'page_translations.title',
                   'page_translations.description',
                   'page_translations.meta_description',
                   'page_translations.headline'])
               ->join('page_translations', 'pages.id', '=', 'page_translations.page_id')
               ->where('code', '=', $url_code)
               ->where('page_translations.language_id', '=', 1)
               ->first();
       }

       return $page;
    }

    public function get_landing_by_path($url_code)
    {
        return DB::table('landings')
            ->select(['landings.id',
                'landings_translations.title',
                'landings_translations.description',
                'landings_translations.prize'])
            ->join('landings_translations', 'landings.id', '=', 'landings_translations.landing_id')
            ->where('landings.url', '=', $url_code)
            ->get()->first();
    }

    public function get_block_by_code($link, $first = false)
    {
        $blocks = DB::table('blocks')
            ->select([
                'block_translations.id',
                'block_translations.description as text',
                'block_translations.url'
            ])
            ->join('block_translations', 'blocks.id', '=', 'block_translations.block_id')
            ->where('block_translations.href', '=', $link);
        if ($first) {
            $blocks = $blocks->first();
        } else {
            $blocks = $blocks->get();
        }

        return $blocks;
    }

    public function get_bonuses_static()
    {
        return DB::table('bonuses')
            ->select('bonuses.*', 'currency.code as currency')
            ->leftJoin('currency', 'bonuses.currency_id', '=', 'currency.id')
            ->whereIn('type_id', [1, 2, 3])
            ->orderBy('id', 'ASC')
            ->get();
    }

    public function set_bonuses($user_id)
    {
        return DB::table('bonus_issue')
            ->leftJoin('bonuses', 'bonus_issue.bonus_id', '=', 'bonuses.id')
            ->leftJoin('freespin_bonus', 'bonus_issue.bonus_id', '=', 'freespin_bonus.id')
            ->leftJoin('currency', 'bonus_issue.currency_id', '=', 'currency.id')
            ->when(request('currency_id', false), function ($query, $currency_id) {
                $query->where('bonus_issue.currency_id', $currency_id);
            })
            ->when(request('status', false), function ($query, $status) {
                $query->where('bonus_issue.status', $status);
            })
            ->when(request('created_at', false), function ($query, $created_at) {
                $query->whereBetween('bonus_issue.created_at', $created_at);
            })
            ->when(request('cat_type', false), function ($query, $cat_type) {
                $query->where('bonus_issue.cat_type', $cat_type);
            })
            ->where('bonus_issue.user_id', '=', $user_id)
            ->select(DB::raw('IFNULL( bonuses.title, freespin_bonus.title) as name'), 'bonuses.title_frontend as title', 'bonus_issue.custom_title', 'freespin_bonus.count', 'bonus_issue.created_at as issued', 'bonus_issue.id', 'bonus_issue.active_until', 'bonus_issue.wagered', 'bonus_issue.to_wager', 'bonus_issue.status', 'bonus_issue.stage', 'bonus_issue.amount', 'bonus_issue.cat_type', 'currency.code as currency')
            ->orderBy('bonus_issue.id', 'DESC')
            ->get();

    }

    public function update_bet($array, $value, $by = 'external_session_id')
    {

        DB::table('games_bets')->where($by, '=', $value)->update(
            $array
        );

    }


    public function get_bet($by, $value, $any = 0, $single = 1, $multi = [])
    {

        if ($any) {
            $bet = DB::table('games_bets')->where($by, '=', $value)->get();
        } elseif ($single) {
            $bet = DB::table('games_bets')->where([
                [$by, '=', $value],
                ['status', '<', 2],
            ])->get();
        } else {
            $bet = DB::table('games_bets')->where(
                $multi
            )->get();
        }

        if (isset($bet[0])) {
            return $bet[0];
        }

        return 0;

    }

    public function get_rollback($by, $value, $any = 0, $single = 1, $multi = [])
    {

        if ($any) {
            $bet = DB::table('rollbacks')->where($by, '=', $value)->get();
        } elseif ($single) {
            $bet = DB::table('rollbacks')->where([
                [$by, '=', $value],
                ['status', '<', 2],
            ])->get();
        } else {
            $bet = DB::table('rollbacks')->where(
                $multi
            )->get();
        }

        if (isset($bet[0])) {
            return $bet[0];
        }

        return 0;

    }

    public function get_transaction($by, $value, $any = 0, $unfilled = 1, $multi = [])
    {

        if ($any) {
            $transaction = DB::table('transactions')->where($by, '=', $value)->get();
        } elseif ($unfilled) {
            $transaction = DB::table('transactions')->where([
                [$by, '=', $value],
                ['status', '<', 2],
            ])->get();
        } else {
            $transaction = DB::table('transactions')->where(
                $multi
            )->get();
        }

        if (isset($transaction[0])) {
            return $transaction[0];
        }

        return 0;

    }

    public function get_transactions($by, $value, $any = 0, $unfilled = 1, $multi = [])
    {

        if ($any) {
            $transactions = DB::table('transactions')->where($by, '=', $value)->get();
        } elseif ($unfilled) {
            $transactions = DB::table('transactions')->where([
                [$by, '=', $value],
                ['status', '<', 2],
            ])->get();
        } else {
            $transactions = DB::table('transactions')->where(
                $multi
            )->get();
        }

        if (isset($transactions[0])) {
            return $transactions;
        }

        return 0;

    }

    public function check_hash($hash, $provider)
    {

        $result = DB::table('hash')->select('id')->where('provider', $provider)->where('hash', '=', $hash)->first();

        return $result;

    }

    public function check_game($provider, $nickname)
    {

        $result = DB::table('games')->select('id')->where('provider', '=', $provider)->where('identer', '=', $nickname)->first();

        return $result;

    }

    public function get_game_session($by = "", $token = "", $simple = 1, $params = 0)
    {

        if ($simple) {
            $game_info = DB::table('game_sessions')->where($by, '=', $token)->orderByDesc('id')->get();
        } elseif (is_array($params)) {
            $game_info = DB::table('game_sessions')->where($params)->orderByDesc('id')->get();
        }

        if (isset($game_info[0])) {
            return $game_info[0];
        }

        return 0;

    }


    public function get_bonus($args = [])
    {
        return DB::table('bonuses')
            ->select('bonuses.*', 'currency.code as currency')
            ->leftJoin('currency', 'bonuses.currency_id', '=', 'currency.id')
            ->where($args)
            ->get();
    }


    public function get_bonus_options($user_id)
    {
        return DB::table('bonuses')
            ->join('bonus_options', 'bonuses.id', '=', 'bonus_options.bonus_id')
            ->select('bonuses.*', 'currency.code as currency', 'bonus_options.id as bonus_option_id')
            ->leftJoin('currency', 'bonuses.currency_id', '=', 'currency.id')
            ->where('bonus_options.player_id', $user_id)
            ->where('enabled', 1)
            ->get();
    }


    public function update_bonus_options($params, $status)
    {
        return DB::table('bonus_options')
            ->where($params)
            ->update(['enabled' => $status]);
    }

    public function get_freespin($args = [])
    {
        return FreespinBonusModel::query()
            ->with('bonus_games')
            ->join('freespin_issue', 'freespin_bonus.id', '=', 'freespin_issue.bonus_id')
            ->select('freespin_bonus.title_frontend', 'freespin_issue.id', 'freespin_issue.created_at', 'currency.code as currency', 'currency.id as currency_id')
            ->leftJoin('currency', 'freespin_bonus.currency_id', '=', 'currency.id')
            ->where($args)
            ->get();
    }

    public function get_user_payment_approve($user_id)
    {
        return DB::table('payments')
            ->where([
                ['user_id', '=', $user_id],
                ['type_id', '=', 3],
                ['status', '=', 1],
            ])
            ->select(DB::raw('Count(payments.id) as count'))->first();
    }

    public function get_last_payment($user_id, $status, $type_id)
    {
        return DB::table('payments')->where('user_id', '=', $user_id)->where('status', '=', $status)->where('type_id', '=', $type_id)->orderBy('id', 'DESC')->first();
    }

    public function get_user_bonus_day($user_id, $now)
    {
        return DB::table('bonuses_user')
            ->where([
                ['user_id', '=', $user_id],
                ['created_at', '>=', $now],
            ])
            ->count();
    }

    public function update_payment($id, $args)
    {
        DB::table('payments')->where('id', '=', $id)->update($args);
    }

    public function get_payments($params)
    {
        return DB::table('transactions')
            ->leftJoin('payments', function ($join) {
                $join->on('transactions.reference_id', '=', 'payments.id');
                $join->where([['transactions.reference_type_id', '=', 5]]);
            })
            ->leftJoin('payment_system', 'payments.payment_system_id', '=', 'payment_system.id')
            ->leftJoin('currency', 'transactions.currency_id', '=', 'currency.id')
            ->whereIn('transactions.reference_type_id', [5, 1])
            ->select('transactions.created_at', 'transactions.id', 'transactions.currency_id', 'transactions.amount', 'transactions.reference_type_id', 'transactions.type_id', 'payments.status', 'payment_system.name as ps', 'currency.code', 'payments.amount_usd')
            ->when(request('created_at', false), function ($query, $created_at) {
                $query->whereBetween('transactions.created_at', $created_at);
            })
            ->when(request('currency_id', false), function ($query, $currency_id) {
                $query->where('transactions.currency_id', $currency_id);
            })
            ->when(request('status', false), function ($query, $status) {
                $query->where('payments.status', $status);
            })
            ->where($params)
            ->limit(20)->orderBy('transactions.id', 'DESC')->get();
    }

    public function get_game_bet_session($user_id, $page = 1)
    {
        return DB::table('games_bets')
            ->Join('games', 'games_bets.game_id', '=', 'games.id')
            ->Join('wallets', 'wallets.id', '=', 'games_bets.wallet_id')
            ->Join('currency', 'wallets.currency_id', '=', 'currency.id')
            ->where('games_bets.user_id', '=', $user_id)
            ->when(request('created_at', false), function ($query, $created_at) {
                $query->whereBetween('games_bets.created_at', $created_at);
            })
            ->when(request('currency_id', false), function ($query, $currency_id) {
                $query->where('currency.id', '=', $currency_id);
            })
            ->select(
                'games_bets.profit',
                'games_bets.bet_sum',
                'currency.code as currency',
                'games.name as game',
                'games_bets.created_at',
                'games_bets.balance_after as balance')
            ->orderBy('games_bets.id', 'DESC')
            ->paginate(100, '', '', $page);
    }

    public function get_user_status($user_id)
    {
        $game_info = DB::table('players')->select('status')->where('id', '=', $user_id)->get();

        if (isset($game_info[0])) {
            return $game_info[0];
        }

        return 0;
    }


    public function get_user_wealth($user_id, $game_id = null)
    {
        $wallet = DB::table('wallets')
            ->select(['wallets.id', 'wallets.balance', 'currency.code', 'currency_id', 'wallets.bonus_balance', 'wallets.locked_balance', DB::raw('(wallets.bonus_balance + wallets.balance) as current_balance, (SELECT(current_balance) + wallets.locked_balance) as withdrawable')])
            ->join('currency', 'currency.id', '=', 'wallets.currency_id')
            ->where([
                ['user_id', '=', $user_id],
                ['primary', '=', 1],
            ])->first();

        if ($game_id) {
            $bonus_balance = DB::table('bonus_issue')->where([
                ['user_id', '=', $user_id],
                ['game_id', '!=', $game_id],
                ['game_id', '>', 0],
                ['status', '=', 2],
                ['currency_id', '=', $wallet->currency_id]
            ])->sum('fixed_amount');

            $wallet->balance = $wallet->balance - $bonus_balance;
        }

        return $wallet;
    }

    public function get_user_wallet($user_id, $wallet_id, $game_id = null)
    {
        $wallet = DB::table('wallets')
            ->select(['wallets.id', 'wallets.balance', 'currency.code', 'currency_id', 'wallets.bonus_balance', 'wallets.locked_balance', 'currency.rate', DB::raw('(wallets.bonus_balance + wallets.balance) as current_balance, (SELECT(current_balance) + wallets.locked_balance) as withdrawable')])
            ->join('currency', 'currency.id', '=', 'wallets.currency_id')
            ->where([
                ['user_id', '=', $user_id],
                ['wallets.id', '=', $wallet_id],
            ])->get()->first();

        if ($game_id) {
            $bonus_balance = DB::table('bonus_issue')->where([
                ['user_id', '=', $user_id],
                ['game_id', '!=', $game_id],
                ['game_id', '>', 0],
                ['status', '=', 2],
                ['currency_id', '=', $wallet->currency_id]
            ])->sum('fixed_amount');

            $wallet->balance = $wallet->balance - $bonus_balance;
        }

        return $wallet;

    }

    public function get_withdraw($user_id)
    {
        return DB::table('payments')
            ->leftJoin('payment_system', 'payments.payment_system_id', '=', 'payment_system.id')
            ->select('payments.created_at', 'payments.id', 'payments.currency_id', 'payments.amount', 'payments.type_id', 'payments.status', 'payment_system.name as ps')
            ->where([
                ['user_id', '=', $user_id],
                ['type_id', '=', 4]
            ])
            ->orderBy('payments.id', 'DESC')
            ->limit(10)
            ->get();
    }

    public function insert_bet(array $array)
    {

        DB::table('games_bets')->insert($array);

        return DB::getPdo()->lastInsertId();

    }

    public function insert_rollback(array $array)
    {

        DB::table('rollbacks')->insert($array);

        return DB::getPdo()->lastInsertId();

    }

    public function get_default_currency()
    {

        $result = DB::table('currency')->select('id')->where('default', '=', 1)->first();

        if (isset($result->id)) {
            return $result->id;
        }

        return 0;

    }

    public function get_single_currency($by, $value, $multi = [])
    {

        if (empty($multi)) {
            $transaction = DB::table('currency')->where($by, '=', $value)->get();
        } else {
            $transaction = DB::table('currency')->where(
                $multi
            )->get();
        }

        if (isset($transaction[0])) {
            return $transaction[0];
        }

        return 0;

    }

    public function get_single_freespin($by, $value, $multi = [])
    {

        if (empty($multi)) {
            $freespin = DB::table('freespin_issue')->where($by, '=', $value)
                ->select('freespin_issue.*', 'freespin_bonus_games.game_id')
                ->join('freespin_bonus_games', 'freespin_issue.bonus_id', '=', 'freespin_bonus_games.fb_id')
                ->get();
        } else {
            $freespin = DB::table('freespin_issue')
                ->select('freespin_issue.*', 'freespin_bonus_games.game_id')
                ->join('freespin_bonus_games', 'freespin_issue.bonus_id', '=', 'freespin_bonus_games.fb_id')
                ->where(
                $multi
            )->get();
        }

        if (isset($freespin[0])) {
            return $freespin[0];
        }

        return 0;

    }

    public function get_single_denomination_currency($by, $value, $multi = [])
    {

        if (empty($multi)) {
            $transaction = DB::table('provider_currency_map')->where($by, '=', $value)->get();
        } else {
            $transaction = DB::table('provider_currency_map')->where(
                $multi
            )->get();
        }

        if (isset($transaction[0])) {
            return $transaction[0];
        }

        return 0;

    }

    public function get_countries()
    {
        return DB::table('countries')->get();
    }

    public function get_currency()
    {
        return DB::table('currency')->get();
    }

    public function get_payment_system()
    {
        return DB::table('payment_system')->leftJoin('currency', 'payment_system.currency_id', '=', 'currency.id')->where('currency.excluded', '!=', 1)->select('payment_system.*', 'currency.rate', 'currency.code', 'currency.name as currency')->orderBy('sorting', 'ASC')->get();
    }

    public function create_wallet($user_id)
    {

        $currency_default_id = $this->get_default_currency();

        $array = [
            'primary' => 1,
            'currency_id' => $currency_default_id,
            'user_id' => $user_id,
            'balance' => 0.00,
            'bonus_balance' => 0.00,
        ];

        DB::table('wallets')->insert($array);

        return DB::getPdo()->lastInsertId();

    }

    public function add_freespins($user, $wallet, $issue_id, $title = "")
    {

        $array = [
            'title' => $title ? "acceptance-test" : "",
            'player_id' => $user->id,
            'currency_id' => 7,
            'wallet_id' => 1218,
            'bonus_id' => 7,
            'issue_code' => $issue_id,
        ];

        DB::table('freespin_issue')->insert($array);

        return DB::getPdo()->lastInsertId();

    }


    public function insert_transaction(array $input)
    {

        DB::table('transactions')->insert($input);

        return DB::getPdo()->lastInsertId();

    }

    public function get_game_type($game_type_name)
    {

        $result = DB::table('game_types')->select('game_type_id')->where('name', '=', $game_type_name)->first();

        return $result;

    }

    public function insert_callback($provider, $body, $headers)
    {

        DB::table('callbacks')->insert(
            ['provider' => $provider, 'body' => $body, 'headers' => $headers, 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')]
        );

    }

    public function insert_game_session($game_id, $user_id, $ident, $token, $url, $big_token = "", $provider_id = null, $wallet_id = null)
    {

        DB::table('game_sessions')->insert(
            ['game_id' => $game_id, 'user_id' => $user_id, 'ident' => $ident, 'token' => $token, 'url' => $url, 'active' => 1, 'last_active_at' => date('Y-m-d H:i:s'), 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s'), 'big_token' => $big_token, 'provider_id' => $provider_id, 'wallet_id' => $wallet_id]
        );

        return DB::getPdo()->lastInsertId();

    }

    public function update_game_session($array, $game_session_id)
    {

        DB::table('game_sessions')->where('id', '=', $game_session_id)->update(
            $array
        );

    }

    public function insert_game($nickname, $gamename, $provider, $game_type_id, $img_path, $game_slug, $provider_id)
    {

        DB::table('games')->insert(
            ['published' => 1, 'provider' => $provider, 'identer' => $nickname, 'name' => $gamename, 'category_id' => $game_type_id, 'img' => $img_path, 'uri' => $game_slug, 'provider_id' => $provider_id]
        );

    }

    public function update_game($nickname, $gamename, $game_id, $provider, $game_type_id, $game_slug, $provider_id)
    {

        DB::table('games')->where('id', '=', $game_id)->update(
            ['published' => 1, 'provider' => $provider, 'name' => $gamename, 'category_id' => $game_type_id, 'identer' => $nickname, 'uri' => $game_slug, 'provider_id' => $provider_id]
        );

    }


    //below is obsolete


    public function get_jackpot()
    {

        $result = DB::table('system')->select('option_value')->where('option_key', 'jackpot')->first();

        $jackpot_value = $result->option_value;

        return $jackpot_value;

    }


    public function update_system($option_key, $option_value)
    {

        DB::table('system')->where('option_key', $option_key)->update(['option_value' => $option_value]);

    }


    public function get_user_likes($user_id)
    {

        $result = DB::table('likes')->select('game_id')->where('user_id', $user_id)->get();

        return $result;

    }

    public function like_game($game_id, $user_id)
    {

        DB::table('likes')->insert(
            ['user_id' => $user_id, 'game_id' => $game_id]
        );

    }

    public function insert_subs($user_id, $type, $value)
    {

        $result = DB::table('checkbox')->select('id')->where('user_id', $user_id)->first();

        if (isset($result->id)) {

            DB::table('checkbox')->where('user_id', $user_id)->update([$type => $value]);

        } else {

            DB::table('checkbox')->insert(
                ['user_id' => $user_id, $type => $value]
            );

        }

    }

    public function get_user_subs($user_id)
    {

        $result = DB::table('checkbox')->where('user_id', $user_id)->limit(1)->first();

        return $result;

    }


    public function set_processed($id)
    {

        DB::table('translations')->where('id', $id)->update(['processed' => 1]);

    }

    public function set_processed_two($id)
    {

        DB::table('translations')->where('id', $id)->update(['processed' => 2]);

    }

    public function update_consent($id, $content)
    {

        DB::table('static')->where('id', $id)->update(['content' => $content]);

    }

    public function insert_translation($data)
    {

        DB::table('translations')->insert(
            ['path' => $data['path'], 'code' => $data['code'], 'translation' => $data['translation'], 'Alpha2Code' => $data['Alpha2Code'], 'translation_id' => 0, 'created' => isset($data['created_at']) ? $data['created_at'] : 0]
        );

    }

    public function insert_watchdog_log($request, $user)
    {

        $user_id = isset($user->UserID) ? $user->UserID : 0;

        DB::table('watchdog')->insert(
            ['content' => json_encode($request->all()), 'route' => json_encode($request->route()), 'user_agent' => json_encode($request->header('User-Agent')), 'ip' => json_encode($request->ip()), 'created' => time(), 'user_id' => $user_id]
        );

    }

    public function insert_watchdog_answer_log($attr = [])
    {

        DB::table('watchdog')->insert(
            ['content' => json_encode($attr['answer']), 'route' => $attr['route'], 'ip' => json_encode($_SERVER['REMOTE_ADDR'])]
        );

    }

    public function get_a_translation()
    {

        $result = DB::table('translations')->where('processed', 0)->limit(1)->get();

        return $result;

    }


    public function addPost($name, $url)
    {

        DB::table('posts')->insert(
            ['post_author' => 1, 'post_title' => $name, 'post_status' => 'published', 'post_type' => 'post']
        );

    }

    public function get_translations()
    {

        $result = DB::table('translations')->get();

        return $result;

    }

    public function get_all_static()
    {

        $result = DB::table('static')->get();

        return $result;

    }

    public function get_jslang()
    {

        $result = DB::table('jslang')->get();

        return $result;

    }

    public function get_translation_by_code($code)
    {

        $result = DB::table('translations')->where('code', $code)->where('Alpha2Code', '!=', 'en')->get();

        return $result;

    }

    public function dislike_game($game_id, $user_id)
    {

        DB::table('likes')->where('game_id', '=', $game_id)->where('user_id', '=', $user_id)->delete();

    }

    public function count_likes($game_id)
    {

        $likes_count = DB::table('likes')->where('game_id', '=', $game_id)->count();

        return $likes_count;

    }

    public function count_templike($game_id)
    {

        $result = DB::table('templike')->select('likes')->where('game_id', '=', $game_id)->first();

        return isset($result->likes) ? $result->likes : 0;

    }

    public function insert_templike($game_id, $likes_count)
    {

        DB::table('templike')->insert(
            ['likes' => $likes_count, 'game_id' => $game_id]
        );

    }

    public function get_forbidden_games()
    {

        $games = DB::table('forbidden')->select('game_name')->get();

        return $games;

    }

    public function get_allowed_games()
    {

        $games = DB::table('allowed_games')->select('game_id')->get();

        return $games;

    }

    public function get_db_languages()
    {

        $languages = DB::table('languages')->get();

        return $languages;

    }

    public function get_db_language_by($array)
    {

        $language_id = 1;

        if (isset($array['country_id'])) {

            $result = DB::table('languages')->select('language_id')->where('country_id', $array['country_id'])->first();

        }

        if (isset($array['country_code'])) {

            $result = DB::table('languages')->select('language_id')->where('country_code', $array['country_code'])->first();

        }


        if (isset($result->language_id)) {
            $language_id = $result->language_id;
        }

        return $language_id;

    }

    public function get_db_language_by_id($country_id)
    {

        $result = DB::table('languages')->where('country_id', $country_id)->first();

        return $result;

    }

    public function get_country_by_code($code)
    {
        return DB::table('countries')->where('code', $code)->first();
    }

    public function get_db_language_by_language_id($language_id)
    {

        $result = DB::table('languages')->where('language_id', $language_id)->first();

        return $result;

    }

    public function update_wallet_primary($user_id, $wallet_id)
    {
        DB::table('wallets')->where('user_id', $user_id)->update(['primary' => 0]);
        DB::table('wallets')->where('id', $wallet_id)->update(['primary' => 1]);
    }

    public function get_user_wallets($user_id)
    {
        return DB::table('wallets')
            ->select(['wallets.id', 'wallets.balance', 'wallets.primary', 'currency.code', 'currency_id', 'wallets.locked_balance', 'wallets.balance as current_balance', DB::raw('(SELECT(current_balance) - wallets.locked_balance) as withdrawable')])
            ->join('currency', 'currency.id', '=', 'wallets.currency_id')
            ->where([
                ['user_id', '=', $user_id],
                ['currency_id', '=', 14]
            ])->orderBy('wallets.id', 'ASC')->get();

    }

    public function get_wallet_id($id)
    {
        return DB::table('wallets')
            ->select()
            ->join('currency', 'currency.id', '=', 'wallets.currency_id')
            ->select('wallets.*', 'currency.code')
            ->where('wallets.id', '=', $id)
            ->first();
    }

    public function get_user_issued_bonus($user_id, $params)
    {
        return DB::table('bonus_issue')
            ->leftJoin('bonuses', 'bonus_issue.bonus_id', '=', 'bonuses.id')
            ->select('bonus_issue.id', 'bonus_issue.wagered', 'bonus_issue.to_wager', 'bonus_issue.locked_amount', 'bonus_issue.fixed_amount', 'bonus_issue.amount', 'bonuses.amount as bonus_ratio', 'bonus_issue.admin_id', 'bonus_issue.currency_id', 'bonus_issue.cat_type')
            ->where([
                ['user_id', '=', $user_id]
            ])
            ->where($params)
            ->orderBy('id', 'ASC')
            ->get();

    }

    public function get_wallet_temp($ps_id, $now)
    {
        $wallet = WalletsTemp::query()->where('payment_system_id', $ps_id)->where('used', 0)->first();
        if ($wallet) {
            $wallet->used = 1;
            $wallet->used_at = $now;
            $wallet->save();
        }
        return $wallet;
    }

    public function check_deposit($params)
    {
        return DB::table('payments')->where($params)->whereIn('status', [2, 5])->first();
    }

    public function select_wallet_temp($params)
    {
        return WalletsTemp::query()->where($params)->first();
    }

    public function update_wallet_temp($address)
    {
        WalletsTemp::query()->where('wallet', $address)->update(['used' => 0]);
    }

    public function insert_payment(array $input)
    {
        DB::table('payments')->insert($input);
        return DB::getPdo()->lastInsertId();

    }

    public function count_deposit($payment_system_id)
    {
        return DB::table('payments')->where([['payment_system_id', '=', $payment_system_id], ['type_id', '=', 3], ['status', '=', 2]])->count();

    }

    public function updated_payment($id, array $input)
    {
        DB::table('payments')->where('id', '=', $id)->update($input);
    }

    public function new_user_wallet($args)
    {
        DB::table('wallets')->insert($args);
        return DB::getPdo()->lastInsertId();
    }

    public function watchdog($args)
    {
        DB::table('watchdog')->insert($args);
    }

    public function freespins_issue($player_id, $currency_id, $amount, $freespin_id, $payment_id, $issue_id)
    {

        $amount = (float)$amount;

        $freespinIssue = \App\Models\FreespinIssue::find($issue_id);
        $freespinIssue->status = 2;
        $freespinIssue->win = $amount;
        $freespinIssue->save();

        $freespin = \App\Models\FreespinBonusModel::find($freespin_id);
        $expiry = new \App\Models\Bonuses();
        $player = \App\Models\Players::find($player_id);
        $freespinGames = $freespin->bonus_games->first();

        $expiry_method = $freespin->duration_type ? $expiry::DURATION[$freespin->duration_type] : null;

        $wager_multiplier = $freespin->wager;

        $currency = \App\Models\Currency::find($currency_id);


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
            'stage' => 2,
            'status' => 2,
            'cat_type' => 2,
            'game_id' => $freespinGames->id ?? null,
            'reference_id' => $payment_id,
            'custom_title' => $freespin->title,
            'created_at' => \Illuminate\Support\Carbon::now(),
            'active_until' => $expiry_method ? $expiry->$expiry_method($freespin->duration) : null,
        ]);

        event(new UpdateBalance($player_id));

        if ($player->affiliate_aid){
            try {
                Http::post("https://affiliates.bitfiring.com/freespin_bonus", [
                    'aid' => $player->affiliate_aid,
                    'player_id' => $player->id,
                    'amount' => $amount,
                    'bonus' => [
                        'id' => $freespin->id,
                        'type_id' => 2,
                        'currency_id' => $freespin->currency_id,
                    ]
                ]);
            }catch (\Exception $exception){

            }
        }
    }

}
