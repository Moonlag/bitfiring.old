<?php

namespace App\Http\Controllers;

use App\Events\GamesProxy;
use App\Models\BonusIssue;
use App\Models\Games;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Mail;
use Response;
use Corcel;
use LaravelLocalization;
use App\Http\Traits\ServiceTrait;
use App\Http\Traits\AuthTrait;
use App\Http\Traits\DBTrait;
use Jenssegers\Agent\Agent;
use App\Models\Game;
use Auth;
use Illuminate\Support\Str;
use App\Http\Traits\OGSTrait;
use App\Http\Traits\IgrosoftTrait;
use App\Http\Traits\SlottyTrait;
use App\Http\Traits\MascotTrait;
use App\Http\Traits\OnlyplayTrait;
use App\Http\Traits\OutcomebetTrait;
use App\Http\Traits\EvoplayTrait;
use App\Http\Traits\BgamingTrait;
use App\Http\Traits\KAgamingTrait;
use App\Http\Traits\PragmaticTrait;
use Carbon\Carbon;
use Cookie;
use Session;

class CasinoController extends Controller
{

    use OGSTrait;
    use IgrosoftTrait;
    use SlottyTrait;
    use DBTrait;
    use ServiceTrait;
    use MascotTrait;
    use OnlyplayTrait;
    use OutcomebetTrait;
    use EvoplayTrait;
    use BgamingTrait;
    use KAgamingTrait;
    use PragmaticTrait;

    public function start(Request $request)
    {
        $this->common_data = \Request::get('common_data');
        $this->games = new Game;

        $agent = new Agent();
        $this->common_data['device_type'] = $agent->isDesktop() ? 1 : 2;
        $this->common_data['i_saw_cookie'] = isset($_COOKIE['i_saw_cookie']) ? 1 : 0;

        try {
            $this->common_data['ip_info'] = get_ip_info($request->ip());
        } catch (\Exception $exception) {
            $this->common_data['ip_info'] = [];
        }
        $languages = \App\Models\Languages::all();
        $this->common_data['provider'] = $this->games->get_game_provider();
        $this->common_data['translate'] = $this->set_translate($request, $languages);

        $locale = $request->segment(1);
        if ($languages->contains('code', $locale)) {
            $this->common_data['head'] = $this->get_static_by_code($request->segment(2), $locale) ?? false;
        } else {
            $locale = 'en';
            $this->common_data['head'] = $this->get_static_by_code($request->segment(1), $locale) ?? false;
        }

        $home = $this->get_landing_by_path(\Str::replace('', '', $request->path()));

        if (!$home) {
            $home = $this->get_static_by_code('home', $locale);
        } else {
            $this->common_data['landing'] = true;
        }

        $this->common_data['home'] = $home;
        $this->common_data['head'] = !!$this->common_data['head'] ? $this->common_data['head'] : $home;

        if ($request->get('aid')) {
            Cookie::queue('aid', $request->get('aid'), 1440);
            $request->query->remove('aid');
            Cookie::queue('query_string', json_encode($request->query()), 1440);
        }


        $this->common_data['currency'] = $this->get_currency();
        $this->common_data['emulated'] = session('emulated') ? route('platform.players.profile', [session('emulated')], false) : null;

        if (isset($this->common_data['user']->id)) {
            $this->common_data['payment_system'] = $this->get_payment_system();

            if (!session('emulated') && ($this->common_data['user']->groups->where('id', '=', 16)->count())) {
                Auth::guard('clients')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                $this->common_data['user'] = '';
                $this->common_data['payment_system'] = '';
            }
        }

        return view('home', ['common_data' => $this->common_data]);

    }

    public function tournaments()
    {

        $this->common_data = \Request::get('common_data');
        $view_name = 'tournaments';

        return view($view_name, ['common_data' => $this->common_data]);


    }

    public function test($static){
       $post = $this->get_static_by_code($static, 'en');

        return view('static_common', ['post' => $post]);
    }

    public function provider($provider_slug)
    {

        $this->common_data = \Request::get('common_data');
        $view_name = 'provider';

        return view($view_name, ['common_data' => $this->common_data]);


    }


    public function file_center(Request $request)
    {

        $flow_file = 'storage/flow.txt';
        $numbers_file = 'storage/numbers.txt';

        $input = $request->all();
        $string = trim($input['string']);
        $part = explode(",", $string);
        $part = $part[0];

        foreach (file($flow_file) as $line) {
            $lastline = trim($line);
            break;
        }

        $numbers = file_get_contents($numbers_file);
        $flow = file_get_contents($flow_file);

        if ($string != $lastline) {

            $numbers = $part . "," . time() . "\r\n" . $numbers;
            $flow = $string . "\r\n" . $flow;

            \File::put($numbers_file, $numbers);
            \File::put($flow_file, $flow);

        }

        $numbers = file_get_contents($numbers_file);
        $flow = file_get_contents($flow_file);

    }

    public function static_page($url_slug)
    {

        $this->common_data = \Request::get('common_data');
        $view_name = 'static_common';

        $post = $this->get_static_by_code($url_slug);

        if (!isset($post->id)) {

            $post = $this->get_static_by_code('404');
            $view_name = '404';

        }

        $this->common_data["page_type"] = "static-page";
        return view($view_name, ['common_data' => $this->common_data, 'post' => $post]);


    }

    public function ajax_static_page(Request $request)
    {
        $locale = $request->get('locale', 'en');
        $slug = $request->get('slug', '404');

        $post = $this->get_static_by_code($slug, $locale);

        if (!isset($post->id)) {
            $post = $this->get_static_by_code('404');
        }

        return response()->json(['post' => $post]);
    }

    public function ajax_bonuses_page(Request $request)
    {
        $bonuses = $this->get_bonuses_static();
        foreach ($bonuses as $post) {

            $variables = array(
                '$max' => $post->max,
                '$currency' => $post->currency,
            );

            $post->description = $this->template_bonuses($post->description, $variables);
        }
        return response()->json(['bonuses' => $bonuses]);
    }

    public function template_bonuses($template, $data): string
    {
        $placeholders = array_map(function ($placeholder) {
            return "{{$placeholder}}";
        }, array_keys($data));

        return strtr($template, array_combine($placeholders, $data));
    }

    public function ajax_game(Request $request)
    {

        $this->common_data = \Request::get('common_data');
        $input = $request->all();

        $provider = $input['provider'];
        $game_url = $input['slug'];

        $this->games = new Game;

        $game = $this->games->get_game_by_play($game_url, $provider);

        if (!isset($game->provider)) {
            return response()->json(['error' => 'Game not Found'], 404);
        }

        if (isset($input['currency_id'])) {
            $isCurrency = $this->games->get_game_by_currency($game->id, $input['currency_id']);
            if (!$isCurrency->id) {
                return response()->json(['error' => 'Currency not Found'], 404);
            }
        }

        if (isset($this->common_data['user']->id)) {
            $model = Games::find($game->id);
            $bonuses = BonusIssue::query()->where('user_id', $this->common_data['user']->id)->where("status", "=", 2)->first();
            if (isset($model->category()->wherePivot('category_id', 38)->first()->id) && isset($bonuses->id)) {
                return response()->json(['wager' => 1]);
            }
            $user_wealth = $this->get_user_wealth($this->common_data['user']->id, $game->id);

            $denomination = $this->get_denomination($user_wealth->currency_id, $game->provider_id);
            $denominator = rtrim($denomination->denomination, 0);
        }


        $token = Str::random(25);
        $big_token = Str::random(50);
        $this->common_data['current_country'] = get_ip_info($request->ip())['countryCode'];
        $this->common_data['game'] = new \stdClass();
        switch ($game->provider) {
            case "pragmatic":

                $this->prepare_pragmaticplay("https://api.prerelease-env.biz/IntegrationService/v3/http/CasinoGameAPI/", "testKey");

                if (isset($this->common_data['user']->id)) {

                    $json = $this->getGame_pragmaticplay([
                        'secureLogin' => 'hscs_bitfiring',
                        'symbol' => $game->identer,
                        'language' => 'en',
                        'token' => $token,
                        'cashierUrl' => 'https://bitfiring.com/deposit',
                        'lobbyUrl' => 'https://bitfiring.com/',
                    ])->getBody();

                    $data = json_decode($json);

                    $result = $data->gameURL;
                    $url = $result;

                    $this->insert_game_session($game->id, $this->common_data['user']->id, $game->identer, $token, $url, $big_token, $game->provider_id, $user_wealth->id);

                } else {

                    $json = $this->getGame_pragmaticplay([
                        'secureLogin' => 'hscs_bitfiring',
                        'playMode' => 'DEMO',
                        'symbol' => $game->identer,
                        'language' => 'en',

                    ])->getBody();

                    $data = json_decode($json);

                    $result = $data->gameURL;
                    $url = $result;

                }

                $this->common_data['game'] = $game;
                $this->common_data['game']->game_url = $url;

                break;
            case "onespinforwin":

                if (isset($this->common_data['user']->id)) {

                    $result = "https://gs.1spin4win.com:10443/gmh5/games.html?game=" . $game->identer . "&hash=" . $token . "&currency=USDT&config=3&freeplay=false&language=en&exit=https://bitfiring.com";

                    $url = $result;

                    $this->insert_game_session($game->id, $this->common_data['user']->id, $game->identer, $token, $url, $big_token, $game->provider_id, $user_wealth->id);

                } else {

                    $result = "https://gs.1spin4win.com:10443/gmh5/games.html?game=" . $game->identer . "&hash=" . $token . "&currency=USDT&config=5&freeplay=true&language=en&exit=https://bitfiring.com";
                    $url = $result;

                }

                $this->common_data['game'] = $game;
                $this->common_data['game']->game_url = $url;

                break;
            case "platipus":
            case "belatra":

                if (isset($this->common_data['user']->id)) {

                    $result = "https://modelplat.com/gm/index.html?key=" . $token . "&gameName=" . $game->identer . "&partner=bitfiring-prod&lang=en";

                    $url = $result;

                    $this->insert_game_session($game->id, $this->common_data['user']->id, $game->identer, $token, $url, $big_token, $game->provider_id, $user_wealth->id);

                } else {

                    $result = "https://modelplat.com/gm/index.html?gameName=" . $game->identer . "&partner=bitfiring-prod&demo=true";
                    $url = $result;

                }

                $this->common_data['game'] = $game;
                $this->common_data['game']->game_url = $url;

                break;
            case "sportgames":

                if (isset($this->common_data['user']->id)) {

                    $result = "https://sportgames-hub-web-preprod.scorum.work?cid=bitfiringcom&productId=" . $game->identer . "&sessionToken=" . $token . "&lang=en&lobbyUrl=https://bitfiring.com";

                    $url = $result;

                    $this->insert_game_session($game->id, $this->common_data['user']->id, $game->identer, $token, $url, $big_token, $game->provider_id, $user_wealth->id);

                } else {

                    $result = "https://sportgames-hub-web-preprod.scorum.work?cid=bitfiringcom&productId=" . $game->identer . "&lang=en&lobbyUrl=https://bitfiring.com";
                    $url = $result;

                }

                $this->common_data['game'] = $game;
                $this->common_data['game']->game_url = $url;

                break;
            case "kagaming":

                if (isset($this->common_data['user']->id)) {

                    $result = "https://gamesna.kaga88.com/?g=" . $game->identer . "&p=BITFIRING&u=player_" . $this->common_data['user']->id . "&t=" . $big_token . "&ak=4DE1FCB1AEEED36CD31B2694B965BC8C&cr=" . $denomination->altercode . "&t=" . $token;
                    $url = $result;

                    $this->insert_game_session($game->id, $this->common_data['user']->id, $game->identer, $token, $url, $big_token, $game->provider_id, $user_wealth->id);

                } else {

                    $result = "https://gamesna.kaga88.com/?g=" . $game->identer . "&p=BITFIRING&u=player_demo&t=player_demo&ak=" . $big_token . "&cr=USD&t=" . $token . "&m=1";
                    $url = $result;

                }


                $this->common_data['game'] = $game;
                $this->common_data['game']->game_url = $url;

                break;
            case "bgaming":

                $this->prepare_bgaming('https://bgaming-network.com/a8r/bitfiring/', 'Pfhv5mqW8PvmsPWc4uaLg7gp');

                $agent = new Agent();
                $device_type = $agent->isDesktop() ? "desktop" : "mobile";
                $device_type_id = $agent->isDesktop() ? 1 : 2;

                if (isset($this->common_data['user']->id)) {

                    $result = $this->bgaming_sessions([
                        "casino_id" => "bitfiring",
                        "game" => $game->identer,
                        "currency" => $denomination->altercode,
                        "locale" => "en",
                        "ip" => "185.181.8.32",
                        "client_type" => "desktop",
                        "balance" => $user_wealth->balance / $denomination->denomination,
                        "urls" => [
                            "return_url" => "https://bitfiring.com",
                            "deposit_url" => "https://bitfiring.com/deposit"
                        ],
                        "user" => [
                            "id" => $denomination->altercode . $this->common_data['user']->id,
                            "firstname" => $this->common_data['user']->firstname,
                            "lastname" => $this->common_data['user']->lastname,
                            "nickname" => "user" . $this->common_data['user']->id,
                            "city" => $this->common_data['user']->city,
                            "date_of_birth" => date('Y-m-d'),
                            "registered_at" => date('Y-m-d'),
                            "gender" => "m",
                            "country" => "CA",
                        ],
                    ])->getBody()->getContents();

                    $result = json_decode($result);

                    $url = $result->launch_options->game_url;

                    $this->insert_game_session($game->id, $this->common_data['user']->id, $game->identer, $token, $url, $result->session_id, $game->provider_id, $user_wealth->id);

                } else {

                    $json = $this->bgaming_demo([
                        'casino_id' => 'bitfiring',
                        'game' => $game->identer,
                        'locale' => 'en',
                        'ip' => '185.181.8.32',
                        'client_type' => 'desktop',
                        'settings' => [
                            "deposit_url" => "https://bitfiring.com/deposit",
                            "return_url" => "https://bitfiring.com",
                        ],
                    ])->getBody()->getContents();

                    $result = json_decode($json);

                }

                $url = $result->launch_options->game_url;

                $this->common_data['game'] = $game;
                $this->common_data['game']->game_url = $url;

                break;
            case "evo":

                $this->prepare_evoplay('http://api.evoplay.games', 3871, 1, 'a9ae64f179729a2b36058306307503a5');

                if (isset($this->common_data['user']->id)) {

                    $data = $this->evo_getUrlGames([
                        "token" => $token,
                        "game" => $game->identer,
                        "settings" => [
                            "user_id" => $this->common_data['user']->id,
                            "exit_url" => "https://bitfiring.com",
                            "cash_url" => "https://bitfiring.com",
                        ],
                        "denomination" => 1,
                        "currency" => $denomination->altercode,
                        "return_url_info" => 1,
                        "callback_version" => 2,
                    ]);
                    if (isset($data->data->link)) {

                        $link = str_replace("http:", "https:", $data->data->link);

                        $this->insert_game_session($game->id, $this->common_data['user']->id, $game->identer, $token, $link, $big_token, $game->provider_id, $user_wealth->id);
                        $this->common_data['game'] = $game;
                        $this->common_data['game']->game_url = $link;

                    }

                } else {

                    $data = $this->evo_getUrlGames([
                        "token" => "demo",
                        "game" => $game->identer,
                        "settings" => [
                            "user_id" => 1,
                            "exit_url" => "https://bitfiring.com",
                            "cash_url" => "https://bitfiring.com",
                        ],
                        "denomination" => 1,
                        "currency" => "usd",
                        "return_url_info" => 1,
                        "callback_version" => 2,
                    ]);

                    if (isset($data->data->link)) {
                        $link = str_replace("http:", "https:", $data->data->link);
                    }

                    $this->common_data['game'] = $game;
                    $this->common_data['game']->game_url = $link;
                }

                if ($this->common_data['current_country'] === 'US') {
                    return response()->json(['proxy' => 1, 'game' => $this->common_data['game']]);
                }

                break;
            case "ogs":

                if (isset($this->common_data['user']->id)) {

                    $session_id = $this->insert_game_session($game->id, $this->common_data['user']->id, $game->identer, $token, "https://p247.live/novomatic-admin/launcher.html?gameName=" . $game_url, $big_token, $game->provider_id, $user_wealth->id);
                    $url = "https://p247.live/novomatic-admin/launcher.html?gameName=" . $game_url . "&userName=" . $this->common_data['user']->id . "&operatorId=16629850&mode=external&sessionId=" . $token . "&closeUrl=https://bitfiring.com&currency=" . $denomination->altercode;

                } else {

                    $url = 'https://p247.live/novomatic-admin/launcher.html?operatorId=16629850&userName=0&sessionId=0&gameName=' . $game_url . '&mode=demo&closeUrl=&lang=en';

                }

                $this->common_data['game'] = $game;
                $this->common_data['game']->game_url = $url;

                break;
            case "ogs_amatic":

                if (isset($this->common_data['user']->id)) {

                    $session_id = $this->insert_game_session($game->id, $this->common_data['user']->id, $game->identer, $token, "https://p247.live/novomatic-admin/launcher.html?gameName=" . $game_url, $big_token, $game->provider_id, $user_wealth->id);
                    $url = "https://p247.live/amatic-admin/launcher/opengame.html?gameName=" . $game->identer . "&mode=external&playerName=" . $this->common_data['user']->id . "&operatorId=16629850&sessionId=" . $token . "&closeUrl=https://bitfiring.com&currency=" . $denomination->altercode;

                } else {

                    $url = 'https://p247.live/amatic-admin/launcher/opengame.html?gameName=' . $game->identer . '&mode=demo&playerName=30951&operatorId=30950&sessionId=30951&lang=en';

                }

                $this->common_data['game'] = $game;
                $this->common_data['game']->game_url = $url;

                break;
            case "ogs_merkur":

                if (isset($this->common_data['user']->id)) {

                    $session_id = $this->insert_game_session($game->id, $this->common_data['user']->id, $game->identer, $token, "https://p247.live/novomatic-admin/launcher.html?gameName=" . $game_url, $big_token, $game->provider_id, $user_wealth->id);
                    $url = "https://p247.live/sunmaker-admin/gamestart.html?gameName=" . $game->identer . "&mode=external&userName=" . $this->common_data['user']->id . "&operatorId=16629850&sessionId=" . $token . "&closeUrl=https://bitfiring.com&currency=" . $denomination->altercode;

                } else {

                    $url = 'https://p247.live/sunmaker-admin/gamestart.html?gameName=' . $game->identer . '&lang=en&mode=demo&userName=30951&operatorId=30950&sessionId=30951';

                }

                $this->common_data['game'] = $game;
                $this->common_data['game']->game_url = $url;

                break;
            case "ogs_zeppelin":

                if (isset($this->common_data['user']->id)) {

                    $session_id = $this->insert_game_session($game->id, $this->common_data['user']->id, $game->identer, $token, "https://p247.live/novomatic-admin/launcher.html?gameName=" . $game_url, $big_token, $game->provider_id, $user_wealth->id);
                    $url = "https://p247.live/solutions-admin/launcher.html?gameName=" . $game->identer . "&mode=external&userName=" . $this->common_data['user']->id . "&operatorId=16629850&sessionId=" . $token . "&closeUrl=https://bitfiring.com&currency=" . $denomination->altercode;

                } else {

                    $url = 'https://p247.live/solutions-admin/launcher.html?gameName=' . $game->identer . '&mode=demo&lang=en&userName=30951&operatorId=30950&sessionId=30951';

                }

                $this->common_data['game'] = $game;
                $this->common_data['game']->game_url = $url;

                break;
            case "ogs_playngo":

                if (isset($this->common_data['user']->id)) {

                    $session_id = $this->insert_game_session($game->id, $this->common_data['user']->id, $game->identer, $token, "https://p247.live/novomatic-admin/launcher.html?gameName=" . $game_url, $big_token, $game->provider_id, $user_wealth->id);

                    $this->prepare_OGS("https://p247.live/euro-extern/dispatcher/egame/", "m5iPmsf4IqPsT9bvpZb5Evm2PPIoC4");

                    $result = $this->launch_game_OGS([
                        "operatorId" => 16629850,
                        "username" => $this->common_data['user']->id,
                        "sessionId" => $token,
                        "gameId" => $game->identer,
                        "closeUrl" => 'https://bitfiring.com',
                    ]);

                    $url = $result->game->url;

                } else {

                    $demo_game_slug = str_replace(" ", "", $game->name);
                    $url = 'https://int.apiforb2b.com/games/' . $demo_game_slug . '.game?operator_id=0&lang=en&user_id=0&auth_token=&currency=';

                }

                $this->common_data['game'] = $game;
                $this->common_data['game']->game_url = $url;

                break;
            case "ogs_yggdrasil":

                if (isset($this->common_data['user']->id)) {

                    $session_id = $this->insert_game_session($game->id, $this->common_data['user']->id, $game->identer, $token, "https://p247.live/novomatic-admin/launcher.html?gameName=" . $game_url, $big_token, $game->provider_id, $user_wealth->id);
                    $url = "https://p247.live/yggdrasil-admin/launcher.html?gameName=" . $game->identer . "&operatorId=16629850&sessionId=" . $token . "&userName=" . $this->common_data['user']->id . "&mode=external&closeUrl=https://bitfiring.com&currency=" . $denomination->altercode;

                } else {

                    $demo_game_slug = str_replace(" ", "", $game->name);
                    $url = 'https://int.apiforb2b.com/games/' . $demo_game_slug . '.game?operator_id=0&lang=en&user_id=0&auth_token=&currency=';

                }

                $this->common_data['game'] = $game;
                $this->common_data['game']->game_url = $url;

                break;
            case "ogs_net_ent":

                if (isset($this->common_data['user']->id)) {

                    $session_id = $this->insert_game_session($game->id, $this->common_data['user']->id, $game->identer, $token, "https://p247.live/novomatic-admin/launcher.html?gameName=" . $game_url, $big_token, $game->provider_id, $user_wealth->id);

                    $this->prepare_OGS("https://p247.live/euro-extern/dispatcher/egame/", "m5iPmsf4IqPsT9bvpZb5Evm2PPIoC4");

                    $result = $this->launch_game_OGS([
                        "operatorId" => 16629850,
                        "username" => $this->common_data['user']->id,
                        "sessionId" => $token,
                        "gameId" => $game->identer,
                        "closeUrl" => 'https://bitfiring.com',
                    ]);

                    $url = $result->game->url;

                } else {

                    $demo_game_slug = str_replace(" ", "", $game->name);
                    $url = 'https://int.apiforb2b.com/games/' . $demo_game_slug . '.game?operator_id=0&lang=en&user_id=0&auth_token=&currency=&home_url=https://bitfiring.com';

                }

                $this->common_data['game'] = $game;
                $this->common_data['game']->game_url = $url;

                break;
            case "slotty_eagaming":
            case "slotty_allwayspin":
            case "slotty_kagaming":
            case "slotty_felix":
            case "slotty_justplay":
            case "slotty_ftg":
            case "slotty_slotexchange":
            case "slotty_bgaming":
            case "slotty":

                $this->prepare_slotty("https://site-ire1.mrslotty.com", "d8b788d5-06de-4807-9f01-a03fdd81081c");

                if (isset($this->common_data['user']->id)) {

                    $session_id = $this->insert_game_session($game->id, $this->common_data['user']->id, $game->identer, "", "", $big_token, $game->provider_id, $user_wealth->id);
                    $json = $this->getGameList_slotty([
                        'action' => 'real_play',
                        'secret' => 'd8b788d5-06de-4807-9f01-a03fdd81081c',
                        'game_id' => $game->identer,
                        'player_id' => $this->common_data['user']->id,
                        'currency' => $denomination->altercode,
                        'return_url' => 'https://bitfiring.com',
                        'deposit_url' => 'https://bitfiring.com/deposit',
                    ])->getBody()->getContents();
                    $result = json_decode($json);
                    $url = $result->response->game_url;

                    $this->update_game_session(["token" => $result->response->token, "url" => $url], $session_id);

                } else {

                    $json = $this->getGameList_slotty([
                        'action' => 'demo_play',
                        'secret' => 'd8b788d5-06de-4807-9f01-a03fdd81081c',
                        'game_id' => $game->identer,
                        'return_url' => 'https://bitfiring.com',
                        'deposit_url' => 'https://bitfiring.com/deposit',
                    ])->getBody()->getContents();

                    $result = json_decode($json);
                    $url = $result->response->game_url;

                }

                $this->common_data['game'] = $game;
                $this->common_data['game']->game_url = $url;

                break;
            case "mascot":

                $this->prepareMascotAPI('https://api.mascot.games/v1/', '../../ssl/apikey.pem');
                //dd($denomination);

                if (isset($this->common_data['user']->id)) {

                    $data = $this->bankGroupSet([
                        "Id" => $denomination->altercode . "_bank_group",
                        "Currency" => $denomination->altercode,
                        "RestorePolicy" => "Last",
                    ]);

                    $this->prepareMascotAPI('https://api.mascot.games/v1/', '../../ssl/apikey.pem');

                    $result = $this->playerSet([
                        "Id" => (string)$denomination->altercode . "_" . $this->common_data['user']->id,
                        "BankGroupId" => $denomination->altercode . "_bank_group",
                    ]);

                    $this->prepareMascotAPI('https://api.mascot.games/v1/', '../../ssl/apikey.pem');

                    $game_session_id = $this->insert_game_session($game->id, $this->common_data['user']->id, $game->identer, "", "", $big_token, $game->provider_id, $user_wealth->id);

                    $data = $this->sessionCreate([
                        "PlayerId" => (string)$denomination->altercode . "_" . $this->common_data['user']->id,
                        "GameId" => (string)$game->identer,
                        "RestorePolicy" => "Restore",
                    ]);

                    $this->update_game_session(["token" => $data['SessionId'], "url" => $data['SessionUrl']], $game_session_id);

                } else {

                    $data = $this->sessionCreateDemo(["BankGroupId" => "BTC_bank_group", "GameId" => $game->identer, "StartBalance" => 10000]);

                }
                $this->common_data['game'] = $game;
                $this->common_data['game']->game_url = $data['SessionUrl'];

                break;
            case "outcomebet_greentube":
            case "outcomebet_igt":
            case "outcomebet_aristocrat":
            case "outcomebet_gaminator":
            case "outcomebet_quickspin":
            case "outcomebet_playtech":
            case "outcomebet_microgaming":
            case "outcomebet_wazdan":
            case "outcomebet_kajot":
            case "outcomebet_booongo":
            case "outcomebet_playson":
            case "outcomebet_konami":
            case "outcomebet_fishing":
            case "outcomebet_egt":
            case "outcomebet_pragmatic":
                $this->prepareOutcomebetAPI('https://api.c27.games/v1/', '../../ssl-outcomebet/apikey.pem');


                if (isset($this->common_data['user']->id)) {

                    $data = $this->bankGroupSet([
                        "Id" => $denomination->altercode . "_bank_group",
                        "Currency" => $denomination->altercode,
                    ]);

                    $this->prepareOutcomebetAPI('https://api.c27.games/v1/', '../../ssl-outcomebet/apikey.pem');

                    $result = $this->playerSet([
                        "Id" => (string)$denomination->altercode . "_" . $this->common_data['user']->id,
                        "BankGroupId" => $denomination->altercode . "_bank_group",
                    ]);

                    $this->prepareOutcomebetAPI('https://api.c27.games/v1/', '../../ssl-outcomebet/apikey.pem');

                    $game_session_id = $this->insert_game_session($game->id, $this->common_data['user']->id, $game->identer, "", "", $big_token, $game->provider_id, $user_wealth->id);

                    $data = $this->sessionCreate([
                        "PlayerId" => (string)$denomination->altercode . "_" . $this->common_data['user']->id,
                        "GameId" => (string)$game->identer,
                        "RestorePolicy" => "Last",
                    ]);

                    $this->update_game_session(["token" => $data['SessionId'], "url" => $data['SessionUrl']], $game_session_id);

                } else {

                    $data = $this->outcomebet_sessionCreateDemo(["BankGroupId" => "BTC_bank_group", "GameId" => $game->identer, "StartBalance" => 10000]);

                }
                $this->common_data['game'] = $game;
                $this->common_data['game']->game_url = $data['SessionUrl'];

                break;
            case "onlyplay":

                $this->prepare_onlyplay('https://int.onlyplay.net/', 'KpaUlkID6KQ55br4Lb6szqkIZDF08f2sjn5NAcPAnZdbpyDcJdaxKM');

                if (isset($this->common_data['user']->id)) {

                    $data = $this->real_onlyplay([
                        "balance" => $user_wealth->balance / $denomination->denomination,
                        "callback_url" => "https://bitfiring.com/wallet/onlyplay",
                        "currency" => $denomination->altercode,
                        "decimals" => $denomination->decimals,
                        "game_bundle" => $game->identer,
                        "lang" => "en",
                        "lobby_url" => "https://bitfiring.com/",
                        "partner_id" => 631,
                        "token" => $big_token,
                        "user_id" => $this->common_data['user']->id,
                    ])->getBody()->getContents();

                    $result = json_decode($data);
                    $url = $result->url;

                    $this->insert_game_session($game->id, $this->common_data['user']->id, $game->identer, $result->session_id, $url, $big_token, $game->provider_id, $user_wealth->id);

                } else {

                    $url = 'https://int.onlyplay.net/integration/request_frame/?game=' . $game->identer . '&partnerId=631&lang=en&balance=50000&currency=fun&lobby_url=https://bitfiring.com/';

                }

                $this->common_data['game'] = $game;
                $this->common_data['game']->game_url = $url;

                break;
        }


        return response()->json(['game' => $this->common_data['game']]);

    }

    public function game($cat_url, $game_url)
    {

        $this->common_data = \Request::get('common_data');

        $this->games = new Game;

        $cat = $this->games->get_game_id_by_type($cat_url);

        if (!isset($cat->game_type_id)) {
            echo 'error 777 CasinoController';
            exit;
        }

        $game = $this->games->get_game($cat->game_type_id, $game_url);
        $this->common_data['game'] = new \stdClass();

        if (isset($this->common_data['user']->id)) {
            $user_wealth = $this->get_user_wealth($this->common_data['user']->id, $game->id);
        }

        $token = Str::random(25);
        $big_token = Str::random(50);

        switch ($game->provider) {
            case "zillion":

                $this->prepare_zillion("https://back.stage.zilliongames.io/api/rexaffiliates/", "ahsgfda6756231hgas7652");

                $agent = new Agent();
                $device_type = $agent->isDesktop() ? "desktop" : "mobile";
                $device_type_id = $agent->isDesktop() ? 1 : 2;

                if (isset($this->common_data['user']->id)) {

                    $result = $this->sessions_zillion([
                        "casino_id" => "housecasino",
                        "game" => $game->identer,
                        "locale" => "en",
                        "currency" => $user_wealth->code,
                        "ip" => "185.181.8.32",
                        "balance" => (int)$user_wealth->balance * 100,
                        "urls" => [
                            "deposit_url" => "https://housecasino.com/deposit",
                            "return_url" => "https://housecasino.com"
                        ],
                        "user" => [
                            "id" => $this->common_data['user']->id,
                            "email" => $this->common_data['user']->email,
                            "firstname" => $this->common_data['user']->firstname,
                            "lastname" => $this->common_data['user']->lastname,
                            "nickname" => "user" . $this->common_data['user']->id,
                            "city" => $this->common_data['user']->city,
                            "country" => "CA",
                            "date_of_birth" => date('Y-m-d'),
                            "gender" => "m",
                            "registered_at" => date('Y-m-d'),
                        ],
                    ])->getBody()->getContents();

                    $result = json_decode($result);

                    $url = $result->launch_options->game_url;

                    $this->insert_game_session($game->id, $this->common_data['user']->id, $game->identer, $token, $url, $big_token);

                } else {

                    $result = $this->demo_zillion([
                        "casino_id" => "housecasino",
                        "game" => $game->identer,
                        "ip" => \Request::ip(),
                        "client_type" => $device_type,
                        "locale" => "en",
                        "urls" => [
                            "deposit_url" => "https://housecasino.com/deposit",
                            "return_url" => "https://housecasino.com"
                        ],
                    ])->getBody()->getContents();

                    $result = json_decode($result);
                    $url = $result->launch_options->game_url;

                }

                $this->common_data['game'] = $game;
                $this->common_data['game']->game_url = $url;

                break;
            case "belatra":

                $this->prepare_belatra("https://test.belatragames.com:34443/api/ss", "r6qkg5b676484923");

                if (isset($this->common_data['user']->id)) {

                    $result = $this->sessions([
                        "casino_id" => "test_housecasino",
                        "game" => $game->identer,
                        "locale" => "en",
                        "currency" => $user_wealth->code,
                        "ip" => "185.181.8.32",
                        "balance" => $user_wealth->balance,
                        "urls" => [
                            "deposit_url" => "https://housecasino.com/deposit",
                            "return_url" => "https://housecasino.com"
                        ],
                        "user" => [
                            "id" => $this->common_data['user']->id,
                            "email" => $this->common_data['user']->email,
                            "firstname" => $this->common_data['user']->firstname,
                            "lastname" => $this->common_data['user']->lastname,
                            "nickname" => "user" . $this->common_data['user']->id,
                            "city" => "user" . $this->common_data['user']->city,
                            "country" => "CA",
                            "date_of_birth" => $this->common_data['user']->dob,
                            "gender" => "m",
                            "registered_at" => $this->common_data['user']->created_at,
                        ],
                    ])->getBody()->getContents();

                    $result = json_decode($result);

                    $url = $result->launch_options->game_url;
                    $this->insert_game_session($game->id, $this->common_data['user']->id, $game->identer, $result->session_id, $url, $big_token);

                } else {

                    $result = $this->demo([
                        "casino_id" => "test_housecasino",
                        "game" => $game->identer,
                        "locale" => "en",
                        "urls" => [
                            "deposit_url" => "https://housecasino.com/deposit",
                            "return_url" => "https://housecasino.com"
                        ],
                    ])->getBody()->getContents();

                    $result = json_decode($result);
                    $url = $result->launch_options->game_url;

                }

                $this->common_data['game'] = $game;
                $this->common_data['game']->game_url = $url;

                break;
            case "booming":

                $this->prepare_booming('https://api.intgr.booming-games.com/', '222222' . time(), 'TTO+X7qkfa5/qH0bbEnH075J2+2P/Q9TpoNT3YhRYGLfEDfPAEcXn+vJU41jAkGP', 'DblvoWy/RwEL7xl2PyADUg==');

                if (isset($this->common_data['user']->id)) {

                    $data = $this->booming_sessions([
                        "game_id" => $game->identer,
                        "balance" => $user_wealth->balance,
                        "currency" => $user_wealth->code,
                        "locale" => "en",
                        "player_id" => $this->common_data['user']->id,
                        "variant" => "desktop",
                        "operator_data" => $big_token,
                        "callback" => "https://housecasino.com/wallet/booming/callback",
                        "rollback_callback" => "https://housecasino.com/wallet/booming/rollback",
                    ]);

                    $this->insert_game_session($game->id, $this->common_data['user']->id, $game->identer, $token, $data->play_url, $big_token);

                } else {

                    $data = $this->booming_sessions([
                        "game_id" => $game->identer,
                        "balance" => "1000.00",
                        "currency" => "EUR",
                        "locale" => "en",
                        "player_id" => 1,
                        "demo" => true,
                        "variant" => "desktop",
                        "callback" => "https://housecasino.com/wallet/booming/callback",
                        "rollback_callback" => "https://housecasino.com/wallet/booming/rollback",
                    ]);

                }

                $this->common_data['game']->game_url = $data->play_url;


                break;
            case "evo":

                $this->prepare_evoplay('http://api.evoplay.games', 2821, 1, 'db638bd47a3eeb04d4aef348c2248c13');

                if (isset($this->common_data['user']->id)) {

                    $data = $this->evo_getUrlGames([
                        "token" => $token,
                        "game" => $game->identer,
                        "settings" => [
                            "user_id" => $this->common_data['user']->id,
                        ],
                        "denomination" => 1,
                        "currency" => $user_wealth->code,
                        "return_url_info" => 1,
                        "callback_version" => 2,
                    ]);

                    if (isset($data->data->link)) {

                        $link = str_replace("http", "https", $data->data->link);

                        $this->insert_game_session($game->id, $this->common_data['user']->id, $game->identer, $token, $link, $big_token);

                        $this->common_data['game']->game_url = $link;

                    }

                } else {

                    $data = $this->evo_getUrlGames([
                        "token" => "demo",
                        "game" => $game->identer,
                        "settings" => [
                            "user_id" => 1,
                        ],
                        "denomination" => 1,
                        "currency" => $user_wealth->code,
                        "return_url_info" => 1,
                        "callback_version" => 2,
                    ]);

                    if (isset($data->data->link)) {
                        $link = str_replace("http", "https", $data->data->link);
                        $this->common_data['game']->game_url = $link;
                    }


                }

                break;
            case "relax":

                if (isset($this->common_data['user']->id)) {

                    $url = "https://d2drhksbtcqozo.cloudfront.net/casino/launcher.html?gameid=" . $game->identer . "&channel=web&moneymode=real&partner=housecasino&partnerid=913&ticket=" . $token;

                    $this->insert_game_session($game->id, $this->common_data['user']->id, $game->identer, $token, $url, $big_token);

                    $this->prepare_relax('https://stag-casino-partner.api.relaxg.net:7000', 'housecasino', 'OIYEAFkiY2VAq8Zm');
                    $this->common_data['game']->game_url = $url;

                } else {
                    $this->prepare_relax('https://stag-casino-partner.api.relaxg.net:7000', 'housecasino', 'OIYEAFkiY2VAq8Zm');
                    $this->common_data['game']->game_url = "https://d2drhksbtcqozo.cloudfront.net/casino/launcher.html?gameid=" . $game->identer . "&channel=web&moneymode=fun&partner=housecasino&partnerid=913";
                }

                break;
            case "spinomenal":

                $this->prepare_spinomenal('https://api-test.spinomenal.com');

                if (isset($this->common_data['user']->id)) {

                    $data = $this->generate_token_spinomenal([
                        "PartnerId" => "housecasino-dev",
                        "GameCode" => $game->identer,
                        "PlatformId" => $device_type_id,
                        "LangCode" => "en_US",
                        "HomeUrl" => "https://housecasino.com/",
                        "ExternalId" => $this->common_data['user']->id,
                        "CurrencyCode" => $user_wealth->code,
                        "TypeId" => 0,
                        "Sig" => md5(date("YmdHis") . $this->common_data['user']->id . "ff898345CCD"),
                        "TimeStamp" => date("YmdHis"),
                    ]);

                    if (!isset($data->GameToken)) {
                        echo '404';
                        exit;
                    }

                    $this->insert_game_session($game->id, $this->common_data['user']->id, $game->identer, $data->GameToken, $data->Url);

                    if (isset($data->Url)) {
                        $this->common_data['game']->game_url = $data->Url;
                    }

                } else {

                    $data = $this->launch_demo_game_spinomenal([
                        "GameCode" => $game->identer,
                        "PartnerId" => "housecasino-dev",
                        "PlatformId" => $device_type_id,
                        "HomeUrl" => "https://housecasino.com/",
                    ]);

                    if (isset($data->Url)) {
                        $this->common_data['game']->game_url = $data->Url;
                    }
                }


                break;
        }

        return view('game', ['common_data' => $this->common_data]);

    }

    public function set_translate($request, $languages)
    {
        if (Session::has('locale')) {
            $locale = Session::get('locale');
        } else {
            $userLangs = preg_split('/,|;/', $request->server('HTTP_ACCEPT_LANGUAGE'));
            foreach ($userLangs as $lang) {
                if (in_array($lang, $languages->pluck('code')->all())) {
                    $locale[] = $lang;
                    Session::push('locale', $lang);
                    break;
                }
            }
        }

        if (!isset($locale) || !in_array('en', $locale)) {
            $locale[] = 'en';
        }

        $translate = [];
        $language = $languages->whereIn('code', $locale);

        $phrase_translate = \App\Models\PhraseTranslations::query()
            ->whereIn('language_id', $language->pluck('id'))
            ->get();

        $block_translate = \App\Models\BlockTranslations::query()
            ->select('href as code', 'description as text', 'language_id', 'id', 'url')
            ->whereIn('language_id', $language->pluck('id'))
            ->get();


        foreach ($language as $lang) {
            if (!isset($translate[$lang->code])) {
                $translate[$lang->code] = [];
            }

            foreach ($phrase_translate->where('language_id', '=', $lang->id) as $item) {
                $translate[$lang->code][$item->code] = $item->text;
            }

            foreach ($block_translate->where('language_id', '=', $lang->id) as $item) {
                if (isset($translate[$lang->code]['block'][$item->code])) {

                    if (!is_array($translate[$lang->code]['block'][$item->code])) {
                        $translate[$lang->code]['block'][$item->code] = [$translate[$lang->code]['block'][$item->code]];
                    }

                    $translate[$lang->code]['block'][$item->code] = [...$translate[$lang->code]['block'][$item->code], $item];
                    continue;
                }

                $translate[$lang->code]['block'][$item->code] = $item;
            }
        }

        return ['locale' => $locale[0], 'translate' => $translate];
    }

}
