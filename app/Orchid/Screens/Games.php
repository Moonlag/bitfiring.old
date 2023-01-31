<?php

namespace App\Orchid\Screens;

use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class Games extends Screen
{
    /**
     * Display header name.
     *
     * @var string
     */
    public $name = 'Games';

    /**
     * Display header description.
     *
     * @var string|null
     */
//    public $description = 'Games';

    /**
     * Query data.
     *
     * @return array
     */
    public function query(): array
    {
        $games = \App\Models\Games::filters()
            ->leftJoin('games_cats', 'games.category_id', '=', 'games_cats.id')
            ->select('games.name as title', 'games.provider', 'games.producer',
                'games_cats.title as category', 'games.identer as identifier',
                'games.devices', 'games.fs', 'games.jp')
            ->paginate(20);

        return ['games' => $games];
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
        return [
            Layout::table('games', [
                TD::make('title', '')
                    ->width('300')
                    ->render(function (){
                        $rand = rand(1, 20);
                        return "<img src='https://picsum.photos/450/200?random={$rand}'
                              alt='sample'
                              class='mw-100 d-block img-fluid'>";
                    })
                    ->sort(),
                TD::make('title', 'Title')->sort(),
                TD::make('provider','Provider')
                   ->sort(),
                TD::make('producer', 'Producer')->sort(),
                TD::make('category', 'Category')
                    ->sort(),
                TD::make('identifier', 'Game Identifier')->sort(),
                TD::make('devices', 'Devices')->sort(),
                TD::make('fs', 'FS')->sort(),
                TD::make('jp', 'JP')->sort(),
                TD::make('balance', 'Languages')->sort(),
            ]),
        ];
    }
}
