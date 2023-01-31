<?php

namespace App\Orchid\Screens\Pages;

use App\Models\FreespinBonusModel;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Screen;
use Orchid\Support\Color;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;

class LandingsEdit extends Screen
{
    /**
     * Display header name.
     *
     * @var string
     */
    public $name = 'LandingsEdit';
    public $description = 'LandingsEdit';
    public $landing_id;
    public $id;
    public $request;

    /**
     * Query data.
     *
     * @return array
     */
    public function query(\App\Models\LandingsTranslations $model): array
    {
        $this->id = $model->id;
        $landing = \App\Models\Landings::find($model->landing_id);
        $this->landing_id = $landing->id;
        return [
            'landings_translations' => $model,
            'landing' => $landing
        ];

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

        return [
            Layout::rows([
                Input::make('landings_translations.title')->required()->title('Title'),
                Select::make('landings_translations.prize')->fromModel(FreespinBonusModel::class, 'title')->required()->title('Prize'),
                Input::make('landing.url')->required()->title('URL'),
                TextArea::make('landings_translations.description')->id('myTextarea')->spellcheck(),
                Button::make('Update')->type(Color::SUCCESS())
                    ->rawClick()
                    ->method('update_code')->parameters([
                        'landing_translations_id' => $this->id,
                        'landing_id' => $this->landing_id
                    ])
            ]),
            Layout::view('orchid.tinymce'),
        ];
    }

    public function update_code(Request $request)
    {
        $input = $request->all();

        \App\Models\Landings::query()
            ->where('id', $input['landing_id'])
            ->update(['url' => $input['landing']['url']]);

        \App\Models\LandingsTranslations::query()
            ->where('id', $input['landing_translations_id'])
            ->update([
                'title' => $input['landings_translations']['title'],
                'description' => $input['landings_translations']['description'],
                'prize' => $input['landings_translations']['prize'],
                'url' => $input['landing']['url'],
            ]);
        Alert::info('You have successfully.');
    }
}
