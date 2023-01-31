<?php

namespace App\Orchid\Screens\Games;

use App\Orchid\Filters\GamesFilter;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
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


    public $permission = [
        'platform.games'
    ];
    /**
     * Query data.
     *
     * @return array
     */
    public function query(): array
    {
        $games = \App\Models\Games::filters()
            ->filtersApply([GamesFilter::class])
            ->leftJoin('games_cats', 'games.category_id', '=', 'games_cats.id')
            ->select('games.name as title', 'games.provider', 'games.producer',
                'games_cats.title as category', 'games.identer as identifier',
                'games.devices', 'games.fs', 'games.jp', 'games.multiplier', 'games.id',)
            ->paginate();

        return ['games' => $games];
    }

    /**
     * Button commands.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): array
    {
        return [

        ];
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
                    ->render(function (\App\Models\Games $model) {
                        $rand = rand(1, 20);
                        return "<a class='d-block' style='width:320px' href='" . route('platform.games.view', $model->id) . "'><img src='/public/uploads/{$model->provider}/{$model->identifier}.webp'
                              alt='sample'
                              class='mw-100 d-block img-fluid'
                              ></a>";
                    })
                    ->canSee(false)
                    ->sort(),
                TD::make('title', 'Title')->render(function (\App\Models\Games $model) {
                    return Link::make($model->title)
                        ->route('platform.games.view', $model->id)
                        ->class('link-primary');
                })->sort(),
                TD::make('provider', 'Provider')
                    ->sort(),
                TD::make('producer', 'Producer')->sort(),
                TD::make('category', 'Category')
                    ->sort(),
                TD::make('identifier', 'Game Identifier')->sort(),
                TD::make('devices', 'Devices')->render(function (\App\Models\Games $model){
                    switch ($model->devices){
                        case 1:
                            $text = 'DM';
                            break;
                        case 2:
                            $text = 'D';
                            break;
                        case 3:
                            $text = 'M';
                            break;
                        default:
                            $text = '';
                    }
                    return "<span class='badge badge-xl light badge-primary'>$text</span>";
                })->sort(),
                TD::make('fs', 'FS')->render(function (\App\Models\Games $model){
                    switch ($model->fs){
                        case 0:
                            $class = 'badge light badge-danger';
                            $text = 'No';
                            break;
                        case 1:
                            $class = 'badge light badge-success';
                            $text = 'Yes';
                            break;
                        default:
                            $class = '';
                            $text = '';
                    }
                    return "<span class='badge-xl {$class}'>$text</span>";
                })->sort(),
                TD::make('jp', 'JP')->render(function (\App\Models\Games $model){
                    switch ($model->jp){
                        case 0:
                            $class = 'badge light badge-danger';
                            $text = 'No';
                            break;
                        case 1:
                            $class = 'badge light badge-success';
                            $text = 'Yes';
                            break;
                        default:
                            $class = '';
                            $text = '';
                    }
                    return "<span class='badge-xl {$class}'>$text</span>";
                })->sort(),
                TD::make('balance', 'Languages')->sort(),
                TD::make('', 'Action')->render(function (\App\Models\Games $model) {
                    return DropDown::make()
                        ->icon('options-vertical')
                        ->list([
                            Link::make('Edit')
                                ->route('platform.games.edit', $model->id)
                                ->class('dropdown-item')
                                ->icon('pencil'),
                            Link::make('View')
                                ->route('platform.games.view', $model->id)
                                ->class('dropdown-item')
                                ->icon('game-controller'),
                            Link::make('Create')
                                ->route('platform.games.create', $model->id)
                                ->class('dropdown-item')
                                ->icon('plus'),
                        ])->class('btn sharp btn-primary tp-btn');;
                })->sort(),
            ]),
        ];
    }

}
