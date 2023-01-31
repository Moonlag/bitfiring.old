<?php

namespace App\Orchid\Screens\Pages;

use App\Models\LandingsTranslations;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;

class Landings extends Screen
{
    /**
     * Display header name.
     *
     * @var string
     */
    public $name = 'Landings';

    /**
     * Query data.
     *
     * @return array
     */
    public function query(): array
    {
        $landing = LandingsTranslations::filters()
            ->select('landings_translations.id','landings_translations.title', 'landings.id as landing_id', 'landings_translations.url', 'languages.code as language')
            ->leftJoin('landings', 'landings_translations.landing_id', '=', 'landings.id')
            ->leftJoin('languages', 'landings_translations.language_id', '=', 'languages.id')
            ->paginate(20);

        return ['landing' => $landing];
    }

    /**
     * Button commands.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): array
    {
        return [
            Link::make('New')
                ->route('platform.landings.new')
                ->class('btn btn-secondary mb-2')
                ->rawClick()
                ->icon('plus'),
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
            Layout::table('landing', [
                TD::make('title', 'Title')->sort(),
                TD::make('url', 'Url')->sort(),
                TD::make('action', '')->render(function (LandingsTranslations $model) {
                    return DropDown::make()
                        ->icon('options-vertical')
                        ->class('btn sharp btn-primary tp-btn')
                        ->list([
                            Link::make('Edit')
                                ->route('platform.landings.edit', $model->id)
                                ->class('dropdown-item')
                                ->rawClick()
                                ->icon('pencil'),
                            Button::make('Delete')
                                ->icon('trash')
                                ->method('landing_delete')
                                ->class('dropdown-item')
                                ->parameters([
                                    'id' => $model->id,
                                    'landing_id' => $model->landing_id
                                ])
                        ]);
                })
            ]),
        ];
    }

    public function landing_delete(Request $request)
    {
        $input = $request->all();

        \App\Models\Landings::query()
            ->where('id', $input['landing_id'])
            ->delete();

        \App\Models\LandingsTranslations::query()
            ->where('id', $input['id'])
            ->delete();

        Alert::info('You have successfully.');
    }
}
