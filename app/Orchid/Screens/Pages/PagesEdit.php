<?php

namespace App\Orchid\Screens\Pages;

use App\Models\Languages;
use App\Models\PageTranslations;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Code;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Quill;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\SimpleMDE;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Screen;
use Orchid\Support\Color;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;

class PagesEdit extends Screen
{
    /**
     * Display header name.
     *
     * @var string
     */
    public $name = 'PagesEdit';

    /**
     * Display header description.
     *
     * @var string|null
     */
    public $description = 'PagesEdit';
    public $page_id;
    public $id;
    public $request;
    public $permission = [
        'platform.pages.edit'
    ];


    public function __construct(Request $request)
    {
        $this->request = $request;
    }


    public function query(\App\Models\Pages $model, $lang): array
    {
        $language = Languages::query()->where('code', '=', $lang)->first();
        $page_translation = PageTranslations::query()->where('page_id', $model->id)->where('language_id', $language->id)->first();

        if (!isset($page_translation->id)) {
            $page_translation = PageTranslations::firstOrCreate(
                [
                    'page_id' => $model->id,
                    'language_id' => $language->id,
                ],
                [
                    'active' => 1,
                    'description' => '',
                    'title' => '',
                    'meta_description' => '',
                    'headline' => '',
                ]);
        }

        $this->id = $page_translation->id;
        $this->page_id = $model->id;
        $this->lang = $lang;
        return [
            'page_translations' => $page_translation,
            'pages' => $model
        ];
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
            Layout::view('orchid.tinymce'),
            Layout::rows([
                Group::make([
                    Select::make('lang')->fromModel(Languages::class, 'code', 'code')->value($this->lang)->title('Current language'),
                    Button::make('Change')->method('swap_language')->class('btn btn-primary btn-sm btn-block')->rawClick()
                ])->alignEnd(),
                Input::make('page_translations.headline')->title('Headline'),
                Input::make('pages.code')->title('Link'),
                Input::make('page_translations.title')->title('Title'),
                Input::make('page_translations.meta_description')->title('Description'),
                Input::make('page_translations.language_id')->type('hidden'),
                TextArea::make('page_translations.description')->id('myTextarea')->spellcheck(),
                Button::make('Update')->type(Color::SUCCESS())
                    ->rawClick()
                    ->method('update_code')->parameters([
                        'page_translations_id' => $this->id,
                        'pages_id' => $this->page_id
                    ])
            ]),
        ];
    }

    public function update_code(Request $request)
    {
        $input = $request->all();
        \App\Models\Pages::query()
            ->where('id', $input['pages_id'])
            ->update(['code' => $input['pages']['code']]);

        \App\Models\PageTranslations::query()
            ->where('id', $input['page_translations_id'])
            ->where('language_id', $input['page_translations']['language_id'])
            ->update([
                'description' => $input['page_translations']['description'],
                'title' => $input['page_translations']['title'],
                'meta_description' => $input['page_translations']['meta_description'],
                'headline' => $input['page_translations']['headline'],
            ]);
        Alert::info('You have successfully.');
    }

    public function swap_language($id, Request $request){
        $input = $request->all();
        return redirect()->route('platform.pages.edit', ['id' => $id, 'lang' => $input['lang']]);
    }
}
