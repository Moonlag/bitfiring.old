<?php

namespace App\Orchid\Screens\Players;

use App\Models\Countries;
use App\Models\Currency;
use App\Models\GamesBets;
use App\Models\PaymentSystem;
use App\Models\User;
use App\Orchid\Filters\TransactionsPlayersFilter;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Fields\DateRange;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class Transactions extends Screen
{
    /**
     * Display header name.
     *
     * @var string
     */
    public $name = 'Transactions';

//    /**
//     * Display header description.
//     *
//     * @var string|null
//     */
//    public $description = 'Transactions';

    /**
     * Query data.
     *
     * @return array
     */
    public function query(Request $request): array
    {
        $transactions = \App\Models\Transactions::filters()
            ->filtersApply([TransactionsPlayersFilter::class])
            ->leftJoin('currency', 'transactions.currency_id', '=', 'currency.id')
            ->leftJoin('players', 'transactions.player_id', '=', 'players.id')
            ->leftJoin('games_bets', 'transactions.reference_id', '=', 'games_bets.id')
            ->leftJoin('games', 'games_bets.game_id', '=', 'games.id')
            ->select('transactions.id',
                'currency.code as currency',
                'transactions.amount',
                'transactions.bonus_part',
                'players.id as player',
                'transactions.created_at',
                'transactions.reference_type_id as reference_type',
                'games.name as reference',
                'games.id as game_id',
                'transactions.reference_id as reference_id'
            )->orderBy('id', 'DESC')
            ->paginate();


        return [
            'transactions' => $transactions,
            'filter' => [
                'group' => [
                    DateRange::make('created_at')
                        ->title('Created at')
                        ->value($request->created_at),

                    Select::make('reference_type')
                        ->empty('No select', '0')
                        ->options([
                            0 => 'All',
                            1 => 'BalanceCorrection',
                            2 => 'Bonuslssue',
                            3 => 'Freespenlssue',
                            4 => 'Game',
                            5 => 'Payment',
                            6 => 'SportsbookBet',
                            7 => 'SportsbookBonuslssue',
                            8 => 'SportsbookFreebet',
                        ])
                        ->title('Reference type')
                        ->value((int)$request->reference_type),

                    Input::make('reference_id')
                        ->type('number')
                        ->title('Reference ID EQ')
                        ->value($request->reference_id),

                    Input::make('account_id')
                        ->type('number')
                        ->title('Account ID EQ')
                        ->value($request->account_id),
                ],
                'action' => [
                    Button::make('Filter')
                        ->vertical()
                        ->icon('filter')
                        ->class('btn btn-primary btn-sm btn-block')
                        ->method('apply_filter'),

                    Button::make('Clear')
                        ->icon('refresh')
                        ->method('clear_filter')
                        ->class('btn btn-sm btn-dark')
                        ->vertical(),
                ]
            ],
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
            Layout::wrapper('orchid.wrapper-col2', [
                'col_left' => [
                    Layout::view('orchid.filter'),
                    Layout::table('transactions', [
                        TD::make('id', 'ID')->sort(),
                        TD::make('amount', 'Amount')->render(function (\App\Models\Transactions $model) {
                            if ($model->amount > 0) {
                                return "<span class='badge badge-lg light  badge-success'>+$model->amount<span>";
                            } elseif ($model->amount < 0) {
                                return "<span class='badge badge-lg light  badge-danger'>$model->amount<span>";
                            } else {
                                return "<span class='badge badge-lg light  badge-dark'>$model->amount<span>";
                            }
                        })
                            ->sort(),
                        TD::make('bonus_part', 'Bonus Part')
                            ->render(function (\App\Models\Transactions $model) {
                                if ($model->bonus_part > 0) {
                                    return "<span class='badge badge-lg light  badge-success'>+$model->bonus_part<span>";
                                } elseif ($model->bonus_part < 0) {
                                    return "<span class='badge badge-lg light  badge-danger'>$model->bonus_part<span>";
                                } else {
                                    return "<span class='badge badge-lg light  badge-dark'>$model->bonus_part<span>";
                                }
                            })->sort(),
                        TD::make('currency', 'Currency')->sort(),
                        TD::make('reference', 'Reference')->render(function (\App\Models\Transactions $model){
                            return $model->game_id ? Link::make($model->reference)->class('link-primary')
                                ->route('platform.games.view', $model->game_id) : '-';
                        })->sort(),
                        TD::make('reference_type', 'Reference Type')->render(function (\App\Models\Transactions $model) {
                            switch ($model->reference_type) {
                                case 1:
                                    $link = 'BalanceCorrection';
                                    $route = '';
                                    break;
                                case 2:
                                    $link = 'BonusIssue';
                                    $route = '';
                                    break;
                                case 3:
                                    $link = 'FreespenIssue';
                                    $route = '';
                                    break;
                                case 4:
                                    $link = 'Game';
                                    break;
                                case 5:
                                    $link = 'Payment';
                                    $route = '';
                                    break;
                                case 6:
                                    $link = 'SportsbookBet';
                                    $route = '';
                                    break;
                                case 7:
                                    $link = 'SportsbookBonuslssue';
                                    $route = '';
                                    break;
                                case 8:
                                    $link = 'SportsbookFreebet';
                                    $route = '';
                                    break;
                                default:
                                    return '';
                            }
                            return Link::make($link . ' ' . $model->reference_id)->route('platform.players.transactions',['reference_type' => $model->reference_type, 'reference_id' => $model->reference_id])->class('link-primary');
                        })->sort(),
                        TD::make('created_at', 'Created at')->render(function (\App\Models\Transactions $model) {
                            return $model->created_at ?? '-';
                        })->sort(),
                        TD::make('player', 'Player')->render(function (\App\Models\Transactions $model) {
                            return Link::make($model->player)->route('platform.players.profile', $model->player)->class('link-primary');
                        })->sort(),
                    ]),
                ],
            ]),
        ];
    }

    public function clear_filter()
    {
        Toast::info(__('Filter cleared'));
        return redirect()->route('platform.players.transactions');
    }

    public function apply_filter(Request $request)
    {
        Toast::success(__('Filter apply'));
        return redirect()->route('platform.players.transactions', array_filter($request->all(), function ($k, $v) {
            return $k != '0' && $v != '_token';
        }, ARRAY_FILTER_USE_BOTH));
    }
}
