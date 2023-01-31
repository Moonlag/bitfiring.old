<?php

namespace App\Orchid\Screens\Marketing;

use App\Models\Transactions;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class GamesPopularityReport extends Screen
{
    /**
     * Display header name.
     *
     * @var string
     */
    public $name = 'GamesPopularityReport';

    /**
     * Display header description.
     *
     * @var string|null
     */
    public $description = 'GamesPopularityReport';

    public $permission = [
        'platform.finance.games-popularity-report'
    ];


    /**
     * Query data.
     *
     * @return array
     */
    public function query(): array
    {
        $transaction = \App\Models\Transactions::query()
            ->leftJoin('games_bets', 'transactions.reference_id', '=', 'games_bets.id')
            ->Join('games', 'games_bets.game_id', '=', 'games.id')
            ->leftJoin('game_provider', 'games.provider_id', '=', 'game_provider.id')
            ->select('transactions.amount',
                'games.id',
                'transactions.created_at',
                'transactions.currency_id',
                'transactions.type_id',
                'transactions.reference_type_id',
                'game_provider.fee as provider_fee',
                'game_provider.title as provider',
                'games.identer',
                'games.name as game',
                'games.devices',
                DB::raw('SUM(transactions.amount) as real_bets, COUNT(DISTINCT(transactions.player_id)) as real_players')
            )->where('transactions.amount', '<', 0)
            ->orderBy('real_bets', 'ASC')
            ->groupBy('games.id')
            ->paginate();

        foreach ($transaction->withQueryString()->items() as $k => $v){
            $transaction->withQueryString()->items()[$k]['idx'] = $k+1;
            $transaction->withQueryString()->items()[$k]['popularity'] = abs($v['real_bets']) / abs(array_sum(array_column($transaction->withQueryString()->items(), 'real_bets'))) * 100;
        }

        return [
            'table' => $transaction
        ];
    }

    /**
     * Button commands.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): array
    {
        return [];
    }

    /**
     * Views.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    public function layout(): array
    {
        return [
            Layout::table('table', [
                TD::make('idx','#'),
                TD::make('provider','Provider'),
                TD::make('identer','Variation'),
                TD::make('game','Title'),
                TD::make('devices', 'Devices')->render(function (Transactions $model){
                    switch ($model->devices){
                        case 1:
                            return 'DM';
                        case 2:
                            return 'D';
                        case 3:
                            return 'M';
                        default:
                            return '-';
                    }
                }),
                TD::make('real_bets','Real bets amount')->render(function (Transactions $model){
                    return bcdiv(abs($model->real_bets),1,2) ?? '-';
                }),
                TD::make('real_players','Real players count'),
                TD::make('popularity','Popularity index')->render(function (Transactions $model){
                    return bcdiv($model->popularity,1,4) ?? '-';
                }),
            ])
        ];
    }
}
