<?php

namespace App\Orchid\Screens\Games;

use App\Models\Currency;
use App\Models\GameDescriptions;
use App\Models\GameLimits;
use App\Models\GameMeta;
use App\Models\Languages;
use App\Orchid\Layouts\ViewPlayerBonusInfoTable;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;

class CreateGame extends Screen
{
    /**
     * Display header name.
     *
     * @var string
     */
    public $name = 'CreateGame';

    /**
     * Display header description.
     *
     * @var string|null
     */
    public $description = 'CreateGame';
    public $id;

    public $permission = [
        'platform.games.create'
    ];
    /**
     * Query data.
     *
     * @return array
     */
    public function query(\App\Models\Games $model): array
    {
        $this->exist = $model->exists;
        if ($this->exist) {
            $this->name = $model->name;
            $this->description = $model->provider;
            $this->id = $model->id;
        }

        $game_limits = Currency::query()
            ->leftJoin('game_limits', function ($join) use ($model) {
                $join->on('currency.id', '=', 'game_limits.currency_id');
                $join->where('game_id', $model->id);
            })
            ->select('currency.id', 'currency.code', 'game_limits.min', 'game_limits.max')
            ->orderBy('id', 'ASC')
            ->get();

        $game_descriptions = Languages::query()
            ->leftJoin('game_descriptions', function ($join) use ($model) {
                $join->on('languages.id', '=', 'game_descriptions.language_id');
                $join->where('game_id', $model->id);
            })
            ->select('languages.id', 'languages.name', 'game_descriptions.value')
            ->orderBy('id', 'ASC')
            ->get();

        $game_meta_description = Languages::query()
            ->leftJoin('game_meta', function ($join) use ($model) {
                $join->on('languages.id', '=', 'game_meta.language_id');
                $join->where('game_id', $model->id);
                $join->where('meta_type', 1);
            })
            ->select('languages.id', 'languages.name', 'game_meta.meta_type', 'game_meta.value')
            ->orderBy('id', 'ASC')
            ->get();

        $game_meta_title = Languages::query()
            ->leftJoin('game_meta', function ($join) use ($model) {
                $join->on('languages.id', '=', 'game_meta.language_id');
                $join->where('game_id', $model->id);
                $join->where('meta_type', 2);
            })
            ->select('languages.id', 'languages.name', 'game_meta.meta_type', 'game_meta.value')
            ->orderBy('id', 'ASC')
            ->get();

        return [
            'game_limits' => $game_limits,
            'game_descriptions' => $game_descriptions,
            'game_meta_description' => $game_meta_description,
            'game_meta_title' => $game_meta_title,
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
            Button::make('Save')
                ->icon('check')
                ->parameters([
                    'game_id' => $this->id
                ])
                ->class('btn btn-secondary mb-2')
                ->method('save_limits'),
            Link::make('Return')
                ->icon('left')
                ->class('btn btn-outline-secondary mb-2')
                ->route('platform.games'),
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

            Layout::wrapper('orchid.games.game_create', [
               'tables' => [
                   Layout::wrapper('orchid.wrapper-table', [
                       'title' => Layout::view('orchid.table-header', ['title' => 'Game limits']),
                       'table' => Layout::table('game_limits', [
                           TD::make('code', 'Currency')->width('10')->align(TD::ALIGN_LEFT),
                           TD::make('', 'Min Bet')->render(function (Currency $model) {
                               return Input::make("game_limits[$model->id][min]")
                                   ->type('number')
                                   ->value($model->min)->class('form-control');
                           })->width('200'),
                           TD::make('', 'Max Bet')->render(function (Currency $model) {
                               return Input::make("game_limits[$model->id][max]")
                                   ->type('number')
                                   ->value($model->max);
                           })->width('200'),
                           TD::make()->render(function (Currency $model) {
                               return Input::make("game_limits[$model->id][currency_id]")
                                   ->type('hidden')
                                   ->value($model->id);
                           }),
                           TD::make()->render(function (Currency $model) {
                               return Input::make("game_limits[$model->id][game_id]")
                                   ->type('hidden')
                                   ->value($this->id);
                           }),
                       ])
                   ]),
                   Layout::wrapper('orchid.wrapper-table', [
                       'title' => Layout::view('orchid.table-header', ['title' => 'Description']),
                       'table' => Layout::table('game_descriptions', [
                           TD::make('name', 'Languages')->width('10')->align(TD::ALIGN_LEFT),
                           TD::make('', 'Description')->render(function (Languages $model) {
                               return Input::make("game_descriptions[$model->id][value]")
                                   ->type('text')
                                   ->value($model->value);
                           }),
                           TD::make()->render(function (Languages $model) {
                               return Input::make("game_descriptions[$model->id][language_id]")
                                   ->type('hidden')
                                   ->value($model->id);
                           }),
                           TD::make()->render(function (Languages $model) {
                               return Input::make("game_descriptions[$model->id][game_id]")
                                   ->type('hidden')
                                   ->value($this->id);
                           }),
                       ])
                   ]),
                   Layout::wrapper('orchid.wrapper-table', [
                       'title' => Layout::view('orchid.table-header', ['title' => 'Meta Description']),
                       'table' => Layout::table('game_meta_description', [
                           TD::make('name', 'Languages')->width('10')->align(TD::ALIGN_LEFT),
                           TD::make('', 'Meta Description')->render(function (Languages $model) {
                               return Input::make("game_meta_description[$model->id][value]")
                                   ->type('text')
                                   ->value($model->value);
                           }),
                           TD::make()->render(function (Languages $model) {
                               return Input::make("game_meta_description[$model->id][language_id]")
                                   ->type('hidden')
                                   ->value($model->id);
                           }),
                           TD::make()->render(function (Languages $model) {
                               return Input::make("game_meta_description[$model->id][game_id]")
                                   ->type('hidden')
                                   ->value($this->id);
                           }),
                           TD::make()->render(function (Languages $model) {
                               return Input::make("game_meta_description[$model->id][meta_type]")
                                   ->type('hidden')
                                   ->value(1);
                           }),
                       ])
                   ]),
                   Layout::wrapper('orchid.wrapper-table', [
                       'title' => Layout::view('orchid.table-header', ['title' => 'Meta Title']),
                       'table' => Layout::table('game_meta_title', [
                           TD::make('name', 'Languages')->width('10')->align(TD::ALIGN_LEFT),
                           TD::make('', 'Meta Title')->render(function (Languages $model) {
                               return Input::make("game_meta_title[$model->id][value]")
                                   ->type('text')
                                   ->value($model->value);
                           }),
                           TD::make()->render(function (Languages $model) {
                               return Input::make("game_meta_title[$model->id][language_id]")
                                   ->type('hidden')
                                   ->value($model->id);
                           }),
                           TD::make()->render(function (Languages $model) {
                               return Input::make("game_meta_title[$model->id][game_id]")
                                   ->type('hidden')
                                   ->value($this->id);
                           }),
                           TD::make()->render(function (Languages $model) {
                               return Input::make("game_meta_title[$model->id][meta_type]")
                                   ->type('hidden')
                                   ->value(2);
                           }),
                       ]),
                   ]),
               ]
            ]),
        ];
    }

    public function save_limits(Request $request)
    {

        try {
            $this->validate($request, [
                'game_id' => ['required', 'integer'],
            ]);

            GameLimits::query()->where('game_id', $request->game_id)->delete();
            $game_limits = array_filter(array_values($request->game_limits), function ($v, $k) {
                return $v['min'] !== null || $v['max'] !== null;
            }, ARRAY_FILTER_USE_BOTH);
            GameLimits::query()->insert($game_limits);

            GameDescriptions::query()->where('game_id', $request->game_id)->delete();
            $game_descriptions = array_filter(array_values($request->game_descriptions), function ($v, $k) {
                return $v['value'] !== null;
            }, ARRAY_FILTER_USE_BOTH);;
            GameDescriptions::query()->insert($game_descriptions);

            GameMeta::query()->where('game_id', $request->game_id)->delete();

            $game_meta_title = array_filter(array_values($request->game_meta_title), function ($v, $k) {
                return $v['value'] !== null;
            }, ARRAY_FILTER_USE_BOTH);;

            $game_meta_description = array_filter(array_values($request->game_meta_description), function ($v, $k) {
                return $v['value'] !== null;
            }, ARRAY_FILTER_USE_BOTH);;
            $game_meta = array_merge($game_meta_title, $game_meta_description);

            GameMeta::query()->insert($game_meta);
        } catch (ValidationException $e) {
            $msg = '';
            foreach ($e->errors() as $key => $value) {
                $msg .= join('', $value);
            }
            Alert::warning($msg);
        }
    }
}
