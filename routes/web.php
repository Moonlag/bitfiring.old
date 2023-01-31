<?php

use App\Http\Controllers\CasinoController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('mail', function (){
    return view('emails.main_template');
});

//seamless API
Route::group(['prefix' => 'wallet'], function () {

	//onespinforwin
    Route::post('onespinforwin/authenticate', 'App\Http\Controllers\OnespinForwinController@authenticate');
    Route::post('onespinforwin/getbalance', 'App\Http\Controllers\OnespinForwinController@getbalance');
    Route::post('onespinforwin/play', 'App\Http\Controllers\OnespinForwinController@play');
    Route::post('onespinforwin/rollback', 'App\Http\Controllers\OnespinForwinController@rollback');

	//infingames
    Route::post('infingame', 'App\Http\Controllers\InfinGameController@process');

	//sportgames
    Route::post('sportgames/playerInfo', 'App\Http\Controllers\SportGamesController@info');
    Route::get('sportgames/health', 'App\Http\Controllers\SportGamesController@health');
    Route::post('sportgames/bet', 'App\Http\Controllers\SportGamesController@bet');
    Route::post('sportgames/win', 'App\Http\Controllers\SportGamesController@win');
    Route::post('sportgames/rollback', 'App\Http\Controllers\SportGamesController@rollback');

    //kagaming
    Route::post('kagaming/start', 'App\Http\Controllers\KAgamingController@start');
    Route::post('kagaming/end', 'App\Http\Controllers\KAgamingController@end');
    Route::post('kagaming/play', 'App\Http\Controllers\KAgamingController@play');
    Route::post('kagaming/credit', 'App\Http\Controllers\KAgamingController@credit');
    Route::post('kagaming/balance', 'App\Http\Controllers\KAgamingController@balance');
    Route::post('kagaming/revoke', 'App\Http\Controllers\KAgamingController@revoke');

    //bgaming
    Route::post('bgaming/play', 'App\Http\Controllers\BgamingController@play');
    Route::post('bgaming/rollback', 'App\Http\Controllers\BgamingController@rollback');
    Route::post('bgaming/freespins', 'App\Http\Controllers\BgamingController@freespins');


    //onlyplay
    Route::post('onlyplay/info', 'App\Http\Controllers\OnlyplayController@info');
    Route::post('onlyplay/bet', 'App\Http\Controllers\OnlyplayController@bet');
    Route::post('onlyplay/win', 'App\Http\Controllers\OnlyplayController@win');
    Route::post('onlyplay/betwin', 'App\Http\Controllers\OnlyplayController@betwin');
    Route::post('onlyplay/cancel', 'App\Http\Controllers\OnlyplayController@cancel');

    //slotty
    Route::get('slotty', 'App\Http\Controllers\SlottyController@play');

    //igrosoft
    Route::post('igrosoft', 'App\Http\Controllers\IgrosoftController@play');

    //ogs
    Route::get('ogs', 'App\Http\Controllers\OGSController@play');

    //belatra
    //Route::post('belatra/play', 'App\Http\Controllers\BelatraController@play');
    //Route::post('belatra/rollback', 'App\Http\Controllers\BelatraController@rollback');

    //zillion
    Route::post('zillion/play', 'App\Http\Controllers\ZillionController@play');
    Route::post('zillion/rollback', 'App\Http\Controllers\ZillionController@rollback');

    //mascot
    Route::post('outcomebet/callback', 'App\Http\Controllers\OutcomebetController@callback');

    //mascot
    Route::post('mascot/callback', 'App\Http\Controllers\MascotController@callback');

    //booming
    Route::post('booming/callback', 'App\Http\Controllers\BoomingController@callback');
    Route::post('booming/rollback', 'App\Http\Controllers\BoomingController@rollback');

    //evo
    Route::post('evo/callback', 'App\Http\Controllers\EvoWalletController@callback');

    //relax
    Route::post('relax/verifyToken', 'App\Http\Controllers\RelaxWalletController@verify_token');
    Route::post('relax/withdraw', 'App\Http\Controllers\RelaxWalletController@withdraw');
    Route::post('relax/deposit', 'App\Http\Controllers\RelaxWalletController@deposit');
    Route::post('relax/rollback', 'App\Http\Controllers\RelaxWalletController@rollback');
    Route::post('relax/getBalance', 'App\Http\Controllers\RelaxWalletController@get_balance');

    //spinomenal
    Route::post('spinomenal/balance', 'App\Http\Controllers\SpinomenalWalletController@get_balance');
    Route::post('spinomenal/process_bet', 'App\Http\Controllers\SpinomenalWalletController@process_bet');
    Route::post('spinomenal/solve_bet', 'App\Http\Controllers\SpinomenalWalletController@solve_bet');

});

Route::group([
    'middleware' => ['common.data', 'web']
], function() {
    Route::match(['GET', 'POST'], 'lucky_wheel/auth', [App\Http\Controllers\WheelRewardController::class, 'auth_player']);
    Route::match(['GET', 'POST'], 'lucky_wheel/spin', [App\Http\Controllers\WheelRewardController::class, 'spin_player']);
});


Route::group([
    //'prefix' => LaravelLocalization::setLocale(),
    'middleware' => ['common.data']
], function () {

    Route::get('/sitemap.xml', 'SitemapController@index');
    Route::get('office/export_this', 'App\Http\Controllers\OfficeController@export_this');

    //slots requests routine
    Route::get('office/refresh_slots_1spin4win', 'App\Http\Controllers\OfficeController@refresh_slots_1spin4win');
    Route::get('office/refresh_slots_infingames', 'App\Http\Controllers\OfficeController@refresh_slots_infingaming');
    Route::get('office/refresh_slots_sportgames', 'App\Http\Controllers\OfficeController@refresh_slots_sportgames');
    Route::get('office/refresh_slots_pragmaticplay', 'App\Http\Controllers\OfficeController@refresh_slots_pragmaticplay');
    Route::get('office/refresh_slots_mancala', 'App\Http\Controllers\OfficeController@refresh_slots_mancala');
    Route::get('office/refresh_slots_onespinforwin', 'App\Http\Controllers\OfficeController@refresh_slots_onespinforwin');
    Route::get('office/refresh_slots_outcomebet', 'App\Http\Controllers\OfficeController@refresh_slots_outcomebet');
    Route::get('office/refresh_slots_slotty', 'App\Http\Controllers\OfficeController@refresh_slots_slotty');
    Route::get('office/refresh_slots_igrosoft', 'App\Http\Controllers\OfficeController@refresh_slots_igrosoft');
    Route::get('office/refresh_slots_belatra', 'App\Http\Controllers\OfficeController@refresh_slots_belatra');
    Route::get('office/refresh_slots_mascot', 'App\Http\Controllers\OfficeController@refresh_slots_mascot');
    Route::get('office/refresh_slots_booming', 'App\Http\Controllers\OfficeController@refresh_slots_booming');
    Route::get('office/refresh_slots_evo', 'App\Http\Controllers\OfficeController@refresh_slots_evo');
    Route::get('office/refresh_slots_relax', 'App\Http\Controllers\OfficeController@refresh_slots_relax');
    Route::get('office/refresh_slots_spinomenal', 'App\Http\Controllers\OfficeController@refresh_slots_spinomenal');
    Route::get('office/refresh_slots_zillion', 'App\Http\Controllers\OfficeController@refresh_slots_zillion');
    Route::get('office/refresh_slots_onlyplay', 'App\Http\Controllers\OfficeController@refresh_slots_onlyplay');
    Route::get('office/refresh_slots_bgaming', 'App\Http\Controllers\OfficeController@refresh_slots_bgaming');
    Route::get('office/refresh_slots_kagaming', 'App\Http\Controllers\OfficeController@refresh_slots_kagaming');

//    Route::get('{static}', [CasinoController::class, 'test'])->name('static');
    Route::get('{vue_capture?}', [CasinoController::class, 'start'])->where('vue_capture', '[\/\w\.\-\ \&\=]*')->name('vue_capture');
});
//admin orchid Feed Exports
Route::get('/exports/{filename?}', [App\Http\Controllers\Exports\ExportController::class, 'get_export_file'])->name('admin.view');


Route::group([
    //'prefix' => LaravelLocalization::setLocale(),
    'middleware' => ['ajax.data']
], function () {

    Route::post('join_first', 'App\Http\Controllers\UserController@join_first');

    Route::post('join_second', 'App\Http\Controllers\UserController@join_confirm');


    Route::post('a/auth', 'App\Http\Controllers\UserController@ajax_auth_confirm');
    Route::post('a/logout', 'App\Http\Controllers\UserController@logout');
    Route::post('a/join_reg', 'App\Http\Controllers\UserController@ajax_registration');
    Route::post('a/change_pass', 'App\Http\Controllers\UserController@ajax_pass_change');

    Route::post('a/user/game_sessions', 'App\Http\Controllers\UserController@player_game_sessions');

    Route::post('a/get_currency', [App\Http\Controllers\UserController::class, 'ajax_currency']);
    Route::post('a/get_ps', [App\Http\Controllers\UserController::class, 'ajax_payment_system']);
    Route::post('a/current_get_ps', [App\Http\Controllers\UserController::class, 'ajax_payment_currency_id']);

    Route::post('a/user/set_wallet', [App\Http\Controllers\UserController::class, 'set_primary_wallet']);
    Route::post('a/user/get_wallets', [App\Http\Controllers\UserController::class, 'get_user_wallet']);
    Route::post('a/user/get_bonuses', [App\Http\Controllers\UserController::class, 'get_user_bonuses_locked']);
    Route::post('a/user/set_freespin', [App\Http\Controllers\FreespinController::class, 'get_freespin']);
    Route::post('a/user/cancel_freespin', [App\Http\Controllers\FreespinController::class, 'cancel_freespin']);
    Route::post('a/user/set_new_wallet', [App\Http\Controllers\UserController::class, 'set_new_user_wallet']);
    Route::post('a/user/transaction', [App\Http\Controllers\UserController::class, 'get_user_translation']);
    Route::post('a/user/withdraws', [App\Http\Controllers\UserController::class, 'ajax_get_withdraw']);
    Route::post('a/user/bonuses', [App\Http\Controllers\UserController::class, 'get_user_bonuses']);
    Route::post('a/user/history_bonuses', [App\Http\Controllers\UserController::class, 'get_bonuses']);
    Route::post('a/user/cancel_bonuses', [App\Http\Controllers\UserController::class, 'cancel_bonuses']);
    Route::post('a/user/active_bonuses', [App\Http\Controllers\UserController::class, 'active_bonuses']);
    Route::post('a/user/freespins', [App\Http\Controllers\UserController::class, 'get_user_freespins']);

    Route::post('a/countries', [App\Http\Controllers\UserController::class, 'ajax_countries']);

    Route::post('a/ajax_status_deposit', [App\Http\Controllers\DepositController::class, 'ajax_status_deposit']);

    Route::get('a/profile', 'App\Http\Controllers\UserController@promotions');
    Route::get('a/profile/settings', 'App\Http\Controllers\UserController@profile');
    Route::get('a/profile/wallet', 'App\Http\Controllers\UserController@wallet');
    Route::get('a/profile/game_history', 'App\Http\Controllers\UserController@game_history');
    Route::get('a/profile/responsible_gaming', 'App\Http\Controllers\UserController@responsible_gaming');
    Route::get('a/profile/support', 'App\Http\Controllers\UserController@support');
    Route::post('a/verify', 'App\Http\Controllers\UserController@ajax_verify_player');
    Route::post('a/profile/update', 'App\Http\Controllers\UserController@update_player');
    Route::post('a/profile/wallets', 'App\Http\Controllers\UserController@player_wallets');

    Route::post('a/profile/session', 'App\Http\Controllers\UserController@profile');
    //transactions
    Route::post('a/deposit', [App\Http\Controllers\DepositController::class, 'ajax_deposit']);
    Route::post('a/cancel_deposit', [App\Http\Controllers\DepositController::class, 'ajax_cancel_deposit']);
    Route::post('a/success_deposit', [App\Http\Controllers\DepositController::class, 'ajax_success_deposit']);
    Route::post('a/withdrawal', 'App\Http\Controllers\UserController@ajax_withdraw');
    Route::post('a/cancel_withdrawal', [App\Http\Controllers\UserController::class, 'ajax_withdraw_cancel']);

    Route::post('a/get_game', 'App\Http\Controllers\CasinoController@ajax_game');
    Route::post('a/static', 'App\Http\Controllers\CasinoController@ajax_static_page');
    Route::post('a/games/main', [\App\Http\Controllers\GamesController::class, 'games_by_main']);
    Route::post('a/games/category', [\App\Http\Controllers\GamesController::class, 'games_by_category']);
    Route::post('a/games/provider', [\App\Http\Controllers\GamesController::class, 'game_by_provider']);
    Route::post('a/games/played', [\App\Http\Controllers\GamesController::class, 'games_by_played']);
    Route::post('a/games/other', [\App\Http\Controllers\GamesController::class, 'games_by_other']);
    Route::post('a/games/search', [\App\Http\Controllers\GamesController::class, 'game_by_search']);
    Route::post('a/games/new', [\App\Http\Controllers\GamesController::class, 'game_by_new']);
    Route::post('a/bonuses', 'App\Http\Controllers\CasinoController@ajax_bonuses_page');
});


//Auth::routes();


