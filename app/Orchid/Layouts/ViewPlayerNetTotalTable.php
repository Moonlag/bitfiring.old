<?php

namespace App\Orchid\Layouts;

use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class ViewPlayerNetTotalTable extends Table
{
    /**
     * Data source.
     *
     * The name of the key to fetch it from the query.
     * The results of which will be elements of the table.
     *
     * @var string
     */
    protected $target = 'net_total';

    protected $title = 'Net total';
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

    /**
     * Get the table cells to be displayed.
     *
     * @return TD[]
     */
    protected function columns(): array
    {
        return [
                TD::make('code', 'Currency')->align(TD::ALIGN_CENTER)->sort(),
                TD::make('total_bets', 'Total bets')->render(function ($net_total) {
                    $amount = usdt_helper($net_total->get('bet_sum'), $net_total->get('code'));

                    $class = $amount < 0 ? 'bg-danger' : ($amount > 0 ? 'bg-success' : 'bg-secondary');
                    return "<span class='rounded px-2 $class'>$amount</span>";
                })->align(TD::ALIGN_CENTER)->sort(),
                TD::make('total_winnings', 'Total wins')->align(TD::ALIGN_CENTER)->render(function ($net_total) {
                    $amount = usdt_helper($net_total->get('payoffs_sum'), $net_total->get('code'));

                    $class = $amount < 0 ? 'bg-danger' : ($amount > 0 ? 'bg-success' : 'bg-secondary');
                    return "<span class='rounded px-2 $class'>$amount</span>";
                })->sort(),
                TD::make('components', 'Bonuses')->render(function ($net_total) {
                    $amount = usdt_helper($net_total->get('fixed_amount'), $net_total->get('code'));

                    $class = $amount < 0 ? 'bg-danger' : ($amount > 0 ? 'bg-success' : 'bg-secondary');
                    return "<span class='rounded px-2 $class'>$amount</span>";
                })->align(TD::ALIGN_CENTER)->sort(),
                TD::make('net_total', 'Net Total')->align(TD::ALIGN_CENTER)->render(function ($net_total) {
                    $ggr = $net_total->get('profit') - $net_total->get('fixed_amount');
                    $amount = usdt_helper($ggr - $net_total->get('bonus_amount'), $net_total->get('code'));

                    $class = $amount < 0 ? 'bg-danger' : ($amount > 0 ? 'bg-success' : 'bg-secondary');
                    return "<span class='rounded px-2 $class'>$amount</span>";
                })->sort(),
                TD::make('payout', 'Payout')->render(function ($net_total) {
                    if($net_total->get('bet_sum')){
                        $amount = usdt_helper($net_total->get('payoffs_sum')/$net_total->get('bet_sum') *100, $net_total->get('code')) ;
                    }else{
                        $amount = 0;
                    }

                    return "<span class='rounded px-2'>$amount%</span>";
                })->align(TD::ALIGN_CENTER)->sort(),
        ];
    }

}
