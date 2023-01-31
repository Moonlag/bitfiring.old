<?php

namespace App\Orchid\Screens\Finance;

use Orchid\Screen\Screen;

class NewCashReport extends Screen
{
    /**
     * Display header name.
     *
     * @var string
     */
    public $name = 'NewCashReport';

    /**
     * Display header description.
     *
     * @var string|null
     */
    public $description = 'NewCashReport';

    public $permission = [
        'platform.finance.new-cash-report'
    ];


    /**
     * Query data.
     *
     * @return array
     */
    public function query(): array
    {
        return [];
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
        return [];
    }
}
