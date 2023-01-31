<?php

namespace App\Orchid\Screens\Marketing;

use Orchid\Screen\Screen;

class PlayersStats extends Screen
{
    /**
     * Display header name.
     *
     * @var string
     */
    public $name = 'PlayersStats';

    /**
     * Display header description.
     *
     * @var string|null
     */
    public $description = 'PlayersStats';

    public $permission = [
        'platform.finance.players-stats'
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
