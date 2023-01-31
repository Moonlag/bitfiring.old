<?php

namespace App\Orchid\Screens\Games;

use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;

class EditGame extends Screen
{
    /**
     * Display header name.
     *
     * @var string
     */
    public $name = 'EditGame';

    /**
     * Display header description.
     *
     * @var string|null
     */
    public $description = 'EditGame';

    public $permission = [
        'platform.games.edit'
    ];

    /**
     * Query data.
     *
     * @return array
     */
    public function query(): array
    {
        return [

        ];
    }


    /**
     * Button commands.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): array
    {
        return [
            Link::make('Return')
                ->icon('left')
                ->class('btn btn-outline-secondary mb-2')
                ->route('platform.games')
        ];
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
