<?php

namespace App\Orchid\Screens\Bonuses;

use App\Models\Currency;
use App\Models\FreespinBonusGamesModel;
use App\Models\Games;
use App\Models\GamesProvider;
use App\Models\IssuePlayerLimit;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Intervention\Image\ImageManagerStatic as Image;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class Bonuses extends Screen
{
    /**
     * Display header name.
     *
     * @var string
     */
    public $name = 'Bonuses';

    /**
     * Display header description.
     *
     * @var string|null
     */
//    public $description = 'Bonuses';

    public $bonus_id;

    public $permission = [
        'platform.bonuses'
    ];

    /**
     * Query data.
     *
     * @return array
     */
    public function query(\App\Models\Bonuses $model, Request $request): array
    {

        return [
            'bonuses' => \App\Models\Bonuses::query()->select('id', 'title', 'description', 'type_id')->orderByDesc('id')->paginate(10),
            'freespins' => \App\Models\FreespinBonusModel::query()->select('id', 'title', 'count')->orderByDesc('id')->paginate(10)
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
            Link::make('Add')->class('btn btn-secondary')->route('platform.bonuses.new')->icon('plus'),
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
            Layout::tabs([
                'Bonuses' => [
                    Layout::table('bonuses', [
                        TD::make('id'),
                        TD::make('title'),
                        TD::make('description'),
                        TD::make()->render(function (\App\Models\Bonuses $model) {
                            return DropDown::make()
                                ->icon('options-vertical')
                                ->list([
                                    Link::make('Edit')
                                        ->route('platform.bonuses.edit', $model->id)
                                        ->class('dropdown-item')
                                        ->icon('pencil'),
                                    Button::make('Delete')
                                        ->method('delete_bonuses')
                                        ->parameters([
                                            'bonuses_id' => $model->id
                                        ])
                                        ->class('dropdown-item')
                                        ->icon('trash')
                                        ->confirm("Are you sure? $model->title Delete"),
                                ])->class('btn sharp btn-primary tp-btn');
                        })
                    ]),
                ],
                'Freespins' => [
                    Layout::table('freespins', [
                        TD::make('id'),
                        TD::make('title'),
                        TD::make('count'),
                        TD::make()->render(function (\App\Models\FreespinBonusModel $model) {
                            return DropDown::make()
                                ->icon('options-vertical')
                                ->list([
                                    Link::make('Edit')
                                        ->route('platform.bonuses.edit', ['id' => $model->id, 'type' => 2])
                                        ->class('dropdown-item')
                                        ->icon('pencil'),
                                    Button::make('Delete')
                                        ->method('delete_bonuses')
                                        ->parameters([
                                            'freesoin_id' => $model->id
                                        ])
                                        ->class('dropdown-item')
                                        ->icon('trash')
                                        ->confirm("Are you sure? $model->title Delete"),
                                ])->class('btn sharp btn-primary tp-btn');
                        })
                    ]),
                ],
            ]),
        ];
    }

    public function delete_bonuses(Request $request)
    {
        $input = $request->all();

        \App\Models\Bonuses::query()->where('id', $input['bonuses_id'])->delete();

        Toast::info(__('Bonus delete'));
    }

    public function delete_freesoin(Request $request)
    {
        $input = $request->all();

        \App\Models\FreespinBonusModel::query()->where('id', $input['freesoin_id'])->delete();

        Toast::info(__('Freespin delete'));
    }
}


