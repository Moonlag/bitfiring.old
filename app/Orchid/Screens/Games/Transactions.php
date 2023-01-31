<?php

namespace App\Orchid\Screens\Games;

use App\Models\GamesBets;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class Transactions extends Screen
{
    /**
     * Display header name.
     *
     * @var string
     */
    public $name = 'Transactions';

    /**
     * Display header description.
     *
     * @var string|null
     */
    public $description = 'Transactions';

    /**
     * Query data.
     *
     * @return array
     */
    public function query(): array
    {
        $games = GamesBets::query()->paginate();
        return [
            'transactions' => $games
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
            Layout::table('transactions', [
                TD::make('title', 'Games')->sort(),
                TD::make('currency', 'Currency')->sort(),
                TD::make('balance_before', 'Balance before')->sort(),
                TD::make('balance_after', 'Balance after')->sort(),
                TD::make('bets_sum', 'Bets sum')->sort(),
                TD::make('payoffs_sum', 'Payoffs sum')->sort(),
                TD::make('profit', 'Profit')->sort(),
                TD::make('bonus_issue', 'Bonus issue')->sort(),
                TD::make('jackpot_win', 'Jackpot win')->sort(),
                TD::make('player', 'Player')->render(function (){
                    return $this->name;
                })->sort(),
                TD::make('created_at', 'Created at')
                    ->render(function (GamesBets $model) {
                        return $model->created_at;
                    })->sort(),
                TD::make('finished_at', 'Finished at')
                    ->render(function (GamesBets $model) {
                        return $model->finished_at;
                    })->sort(),
                TD::make('action', '')->render(function () {
                    return DropDown::make()
                        ->icon('options-vertical')
                        ->list([
                            Button::make(__('view'))->class('dropdown-item disabled')
                                ->confirm(__('Are you sure you want to change status state?')),
                        ]);
                })
            ]),
        ];
    }
}
