<?php

namespace App\Orchid\Screens\Marketing;

use Orchid\Screen\Screen;

class BonusesReport extends Screen
{
    /**
     * Display header name.
     *
     * @var string
     */
    public $name = 'BonusesReport';

    /**
     * Display header description.
     *
     * @var string|null
     */
    public $description = 'BonusesReport';

    public $permission = [
        'platform.finance.bonuses-report'
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
