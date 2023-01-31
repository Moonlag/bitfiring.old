<?php

namespace App\Orchid\Layouts;

use Orchid\Screen\Actions\Link;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class ViewPlayerLimitsTable extends Table
{
    /**
     * Data source.
     *
     * The name of the key to fetch it from the query.
     * The results of which will be elements of the table.
     *
     * @var string
     */
    protected $target = 'limits';

    protected $title = 'Limits';

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
            Link::make('Account Limit')
                ->route('platform.players.limits', $this->query->get('player')->id)
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
            TD::make('type_name', 'Type name')->align(TD::ALIGN_CENTER)->sort(),
            TD::make('status', 'Status')->align(TD::ALIGN_CENTER)
                ->sort(),
            TD::make('period_name', 'Period')->align(TD::ALIGN_CENTER)->sort(),
            TD::make('account_limits', 'Account limits')->align(TD::ALIGN_CENTER)
                ->sort(),
            TD::make('current_values', 'Current Values')->align(TD::ALIGN_CENTER)->sort(),
            TD::make('amount', 'Amount')->align(TD::ALIGN_CENTER)->sort(),
            TD::make('confirm_until', 'Confirm Until')->align(TD::ALIGN_CENTER)->sort(),
            TD::make('created_at', 'Created at')->align(TD::ALIGN_CENTER)
                ->render(function ($limits) {
                    return $limits->created_at;
                })->sort(),
            TD::make('disabled_at', 'Disabled at')->align(TD::ALIGN_CENTER)->sort(),
        ];
    }

}
