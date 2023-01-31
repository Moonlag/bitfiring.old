<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use \App\Http\Controllers\GamesController;
use \App\Http\Controllers\Api\CryptoApiCallback;
use \App\Http\Controllers\WheelRewardController;
use \Carbon\Carbon;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::match(['GET', 'POST'],'games', [GamesController::class, 'getGames']);
Route::match(['GET', 'POST'],'translate', [\App\Http\Controllers\Api\LocaleApi::class, 'loadLocaleTranslate']);
Route::match(['GET', 'POST'],'winners', [GamesController::class, 'winnersBet']);
Route::match(['GET', 'POST'],'provider', [GamesController::class, 'activeProvider']);
Route::match(['GET', 'POST'],'bet', function (Request $request){
    $input = $request->all();
    $bets = \App\Models\GamesBets::query()
        ->where('user_id', $input['player_id'])
        ->when(request('created_at', false), function ($builder, $created_at){
            $now = Carbon::now();
            $builder->whereBeetwen('created_at', [$created_at, $now]);
        })
        ->when(request('bet_id', false), function ($builder, $bet){
            $builder->whereNotIn('id', $bet);
        })
        ->get();
    return $bets->map(function ($item, $key){
        $rate = (float)$item->wallets->currency->rate;
        $item->profit = $item->profit / $rate;
        return $item;
    });

});
Route::match(['POST'], 'callback/payment/withdrawal', [CryptoApiCallback::class, 'payment_withdrawal']);
Route::match(['POST'], 'callback/payment/withdrawal-reject', [CryptoApiCallback::class, 'payment_withdrawal_reject']);

Route::group([
    'middleware' => ['common.data', 'web']
], function() {
    Route::match(['GET', 'POST'], 'lucky_wheel/phase', [WheelRewardController::class, 'check_phase']);
    Route::match(['GET', 'POST'], 'lucky_wheel/rewards', [WheelRewardController::class, 'set_rewards']);
});

