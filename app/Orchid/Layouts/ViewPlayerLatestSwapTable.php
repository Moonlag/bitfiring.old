<?php

namespace App\Orchid\Layouts;

use Orchid\Screen\Actions\Link;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class ViewPlayerLatestSwapTable extends Table
{
    /**
     * Data source.
     *
     * The name of the key to fetch it from the query.
     * The results of which will be elements of the table.
     *
     * @var string
     */
    protected $target = 'latest_swaps';

    protected $title = 'Latest Swaps';

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
            Link::make('All Swaps')
                ->route('platform.players.swap', $this->query->get('player')->id)
                ->icon('link')->class('btn btn-rounded btn-outline-primary btn-sm'),
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
            TD::make('from_id', 'From Currency')
                ->render(function ($latest_swaps){
                    $currency = \App\Models\Currency::find($latest_swaps->from_id);
                    return $currency->code;
                })
                ->align(TD::ALIGN_CENTER)->sort(),
            TD::make('from_amount', 'From Sum')
                ->render(function ($latest_swaps) {
                    $currency = \App\Models\Currency::find($latest_swaps->from_id);
                    $balance = usdt_helper($latest_swaps->from_amount, $currency->code);
                    $subbalance = ($currency->code !== 'USDT' ? ' (' . usdt_helper($latest_swaps->from_amount / $currency->rate, 'USDT') . ' USDT)' : '');
                    return   "<div class='d-flex flex-column'>
                    <span>$balance</span>
                    <span style='font-size: 12px'>$subbalance</span>
                </div>";
                })
                ->align(TD::ALIGN_CENTER)->sort(),
            TD::make('to_id', 'To Currency')
                ->render(function ($latest_swaps){
                    $currency = \App\Models\Currency::find($latest_swaps->to_id);
                    return $currency->code;
                })
                ->sort(),
            TD::make('to_amount', 'To Sum')
                ->render(function ($latest_swaps) {
                    $currency = \App\Models\Currency::find($latest_swaps->to_id);
                    $balance = usdt_helper($latest_swaps->to_amount, $currency->code);
                    $subbalance = ($currency->code !== 'USDT' ? ' (' . usdt_helper($latest_swaps->to_amount / $currency->rate, 'USDT') . ' USDT)' : '');
                    return   "<div class='d-flex flex-column'>
                    <span>$balance</span>
                    <span style='font-size: 12px'>$subbalance</span>
                </div>";
                })
                ->sort(),
            TD::make('status', 'Status')->render(function () {
                return "<span class='badge light badge-success'>Approved</span>";
            })->align(TD::ALIGN_CENTER)->sort(),
            TD::make('created_at', 'Finished at')->align(TD::ALIGN_CENTER)->render(function ($latest_swaps) {
                return $latest_swaps->created_at ?? '-';
            })->sort(),
        ];
    }

}
