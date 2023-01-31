<?php

namespace App\Orchid\Screens\Marketing;

use Orchid\Screen\Screen;

class PlayersSummaryReport extends Screen
{
    /**
     * Display header name.
     *
     * @var string
     */
    public $name = 'PlayersSummaryReport';

    /**
     * Display header description.
     *
     * @var string|null
     */
    public $description = 'PlayersSummaryReport';

    public $permission = [
        'platform.finance.players-summary-report'
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
