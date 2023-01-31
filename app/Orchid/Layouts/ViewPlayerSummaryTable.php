<?php

namespace App\Orchid\Layouts;

use Orchid\Screen\Actions\Link;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;
use Auth;

class ViewPlayerSummaryTable extends Table
{
    /**
     * Data source.
     *
     * The name of the key to fetch it from the query.
     * The results of which will be elements of the table.
     *
     * @var string
     */
    protected $target = 'summary';

    protected $title = 'Summary';

    /**
     * @return string
     */
    protected function textNotFound(): string
    {
        return __('No data found');
    }


    /**
     * @return string
     */
    protected function iconNotFound(): string
    {
        return 'table';
    }

    /**
     * Enable a hover state on table rows.
     *
     * @return bool
     */
    protected function hoverable(): bool
    {
        return true;
    }

    protected function striped(): bool
    {
        return true;
    }

    protected function action()
    {
        return [
            Link::make('Change Balance')
                ->route('platform.players.balance', $this->query->get('player')->id)
                ->icon('link')->class('btn btn-rounded btn-outline-primary btn-sm')
        ];
    }

    /**
     * Get the table cells to be displayed.
     *
     * @return TD[]
     */
    protected function columns(): array
    {

        return [
            TD::make('code', 'Currency')->align(TD::ALIGN_CENTER)->sort(),
            TD::make('balance', 'Balance')->align(TD::ALIGN_CENTER)->render(function ($summary) {
                $balance = usdt_helper($summary->get('balance'), $summary->get('code'));
                return "<div class='d-flex flex-column'>
                    <span>$balance</span>
                </div>";
            })->sort(),
            TD::make('deposit_sum', 'Deposits Sum')->align(TD::ALIGN_CENTER)->render(function ($summary) {
                $balance = usdt_helper($summary->get('deposit_sum'), $summary->get('code'));
                return "<div class='d-flex flex-column'>
                    <span>$balance</span>
                </div>";
            })->sort(),
            TD::make('cashouts_sum', 'Cashouts sum')->align(TD::ALIGN_CENTER)->render(function ($summary) {
                $balance = usdt_helper($summary->get('cashouts_sum'), $summary->get('code'));
                return "<div class='d-flex flex-column'>
                    <span>$balance</span>
                </div>";
            })->sort(),
            TD::make('pending_cashouts_sum', 'Pending Cashouts Sum')->align(TD::ALIGN_CENTER)->render(function ($summary) {
                $balance = usdt_helper($summary->get('pending_cashouts_sum'), $summary->get('code'));
                return "<div class='d-flex flex-column'>
                    <span>$balance</span>
                </div>";
            })->sort(),
            TD::make('corrections', 'Corrections sum')->align(TD::ALIGN_CENTER)->render(function ($summary) {
                $balance = usdt_helper($summary->get('corrections'), $summary->get('code'));
                return "<div class='d-flex flex-column'>
                    <span>$balance</span>
                </div>";
            })->sort(),
            TD::make('gifts_sum', 'Gifts Sum')->align(TD::ALIGN_CENTER)->render(function ($summary) {
                return usdt_helper($summary->get('gifts_sum'), $summary->get('code'));
            })->sort(),
            TD::make('ngr', 'NGR')->align(TD::ALIGN_CENTER)
                ->render(function ($summary) {
                    if ($summary->get('bonus_amount')) {
                        $amount = $summary->get('locked_amount') ? usdt_helper($summary->get('profit') * ($summary->get('bonus_amount') / $summary->get('locked_amount')) - $summary->get('fixed_amount'), $summary->get('code')) : 0;
                    } else {
                        $amount = usdt_helper($summary->get('profit'), $summary->get('code'));
                    }
//                    $amount = $summary->get('locked_amount') ? usdt_helper($summary->get('bet_sum') * ($summary->get('bonus_amount') / $summary->get('locked_amount')) - $summary->get('fixed_amount'), $summary->get('code')) : 0;
                    $class = $amount < 0 ? 'bg-danger' : ($amount > 0 ? 'bg-success' : 'bg-secondary');
                    return "<span class='rounded px-2 $class'>$amount</span>";
                })->sort(),
            TD::make('spent_in_casino', 'Spent in Casino')->align(TD::ALIGN_CENTER)
                ->render(function ($summary) {
                    $amount = usdt_helper($summary->get('deposit_sum') - $summary->get('cashouts_sum') - $summary->get('pending_cashouts_sum') - $summary->get('balance') - $summary->get('corrections') - $summary->get('gifts_sum') , $summary->get('code'));

                    $class = $amount < 0 ? 'bg-danger' : ($amount > 0 ? 'bg-success' : 'bg-secondary');
                    return "<span class='rounded px-2 $class'>$amount</span>";
                })->sort(),
//            TD::make('payout', 'Payout')->align(TD::ALIGN_CENTER)
//                ->render(function ($summary) {
//                    if($summary->get('bet_sum')){
//                        $amount = usdt_helper($summary->get('payoffs_sum')/$summary->get('bet_sum') *100, $summary->get('code')) ;
//                    }else{
//                        $amount = 0;
//                    }
//
//                    return "<span class='rounded px-2'>$amount%</span>";
//                })->sort(),
            TD::make('bet_sum', 'bet_sum')->render(function ($summary) {
                return usdt_helper($summary->get('bet_sum'), $summary->get('code'));
            })->canSee(Auth::user()->id === 2 || Auth::user()->id === 16),
            TD::make('payoffs_sum', 'payoffs_sum')->render(function ($summary) {
                return usdt_helper($summary->get('payoffs_sum'), $summary->get('code'));
            })->canSee(Auth::user()->id === 2 || Auth::user()->id === 16),
            TD::make('profit', 'profit')->render(function ($summary) {
                return usdt_helper($summary->get('profit'), $summary->get('code'));
            })->canSee(Auth::user()->id === 2 || Auth::user()->id === 16),
            TD::make('bonus_amount', 'bonus_amount')->canSee(Auth::user()->id === 2 || Auth::user()->id === 16),
            TD::make('locked_amount', 'locked_amount')->canSee(Auth::user()->id === 2 || Auth::user()->id === 16),
            TD::make('fixed_amount', 'fixed_amount')->canSee(Auth::user()->id === 2 || Auth::user()->id === 16),
            TD::make('bonus_count', 'Bonuses')->align(TD::ALIGN_CENTER)
                ->render(function ($summary) {
                    return usdt_helper($summary->get('bonus_amount'), $summary->get('code'));
                })->sort(),
            TD::make('bonus_ration', 'Bonus ratio')->align(TD::ALIGN_CENTER)->render(function ($summary) {
                return usdt_helper($summary->get('deposit_sum') != 0 ? (($summary->get('bonus_amount') / $summary->get('deposit_sum')) * 100) : '0.00', 'USDT') . '%';
            })->sort(),
        ];
    }

}
