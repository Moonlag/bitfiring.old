<?php

namespace App\Orchid\Screens\Pages;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Screen;
use Orchid\Support\Color;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;

class PagesNew extends Screen
{
    /**
     * Display header name.
     *
     * @var string
     */
    public $name = 'Page New';

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
                Input::make('page.headline')->required()->title('Headline'),
                Input::make('page.code')->required()->title('Link'),
                Input::make('page.title')->required()->title('Title'),
                Input::make('page.meta_description')->required()->title('Description'),
                TextArea::make('page.description')->id('myTextarea')->spellcheck(),
                Button::make('Create')
                    ->type(Color::SUCCESS())
                    ->method('update_code')->parameters(['id' => 1])
            ]),
            Layout::view('orchid.tinymce'),
        ];
    }

    public function update_code(Request $request)
    {
        $input = $request->all();
        $input = $input['page'];
     
        $validator = Validator::make($input, [
            'headline' => 'required',
            'code' => 'required',
            'title' => 'required',
            'meta_description' => 'required',
        ]);

        if ($validator->fails()) {
            \Orchid\Support\Facades\Toast::error("Validation Error, " . array_keys($validator->failed())[0] . " ");
        } else {

            $page = \App\Models\Pages::query()
                ->create([
                    'code' => $input['code'],
                    'published' => 1
                ]);

            \App\Models\PageTranslations::query()
                ->create([
                    'page_id' => $page->id,
                    'language_id' => 1,
                    'active' => 1,
                    'description' => $input['description'],
                    'title' => $input['title'],
                    'meta_description' => $input['meta_description'],
                    'headline' => $input['headline'],
                ]);

            Alert::info('You have successfully.');

            return redirect()->route('platform.pages');
        }
    }
}
