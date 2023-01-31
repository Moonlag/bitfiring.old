<?php

namespace App\Orchid\Layouts;

use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class ViewPlayerBinInfoTable extends Table
{
    /**
     * Data source.
     *
     * The name of the key to fetch it from the query.
     * The results of which will be elements of the table.
     *
     * @var string
     */
    protected $target = 'bin_info';


    protected $title = 'BIN info';
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
                TD::make('system', 'System')->align(TD::ALIGN_CENTER)->sort(),
                TD::make('account', 'Account')->align(TD::ALIGN_CENTER)->sort(),
                TD::make('bank_name', 'Bank name')->align(TD::ALIGN_CENTER)->sort(),
                TD::make('bank_country', 'Bank country')->align(TD::ALIGN_CENTER)->sort(),
                TD::make('stage', 'Stage')->align(TD::ALIGN_CENTER)->sort(),
                TD::make('card_type', 'Card type')->align(TD::ALIGN_CENTER)->sort(),
        ];
    }

}
