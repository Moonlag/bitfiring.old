<?php

namespace App\Orchid\Screens\Marketing;

use Orchid\Screen\Screen;

class PlayersLimits extends Screen
{
    /**
     * Display header name.
     *
     * @var string
     */
    public $name = 'PlayersLimits';

    /**
     * Display header description.
     *
     * @var string|null
     */
    public $description = 'PlayersLimits';

    public $permission = [
        'platform.finance.players-limits'
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
