<?php

namespace App\Orchid\Screens\Pages;

use App\Models\FreespinBonusModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Screen;
use Orchid\Support\Color;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;

class LandingsNew extends Screen
{
    /**
     * Display header name.
     *
     * @var string
     */
    public $name = 'LandingsNew';

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
        return [
            Layout::rows([
                Input::make('landing.title')->required()->title('Title'),
                Select::make('landing.prize')->fromModel(FreespinBonusModel::class, 'title')->required()->title('Prize'),
                Input::make('landing.url')->required()->title('URL'),
                TextArea::make('landing.description')->id('myTextarea')->spellcheck(),
                Button::make('Create')
                    ->type(Color::SUCCESS())
                    ->method('update_code')
            ]),
            Layout::view('orchid.tinymce'),
        ];
    }

    public function update_code(Request $request)
    {
        $input = $request->all();
        $input = $input['landing'];

        $validator = Validator::make($input, [
            'title' => 'required',
            'prize' => 'required',
            'url' => 'required',
        ]);

        if ($validator->fails()) {
            \Orchid\Support\Facades\Toast::error("Validation Error, " . array_keys($validator->failed())[0] . " ");
        } else {

            $page = \App\Models\Landings::query()
                ->create([
                    'url' => $input['url'],
                    'published' => 1
                ]);

            \App\Models\LandingsTranslations::query()
                ->create([
                    'landing_id' => $page->id,
                    'title' => $input['title'],
                    'language_id' => 1,
                    'active' => 1,
                    'description' => $input['description'],
                    'prize' => $input['prize'],
                    'url' => $input['url'],
                ]);

            Alert::info('You have successfully.');

            return redirect()->route('platform.landings');
        }
    }
}
