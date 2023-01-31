<?php

namespace App\Orchid\Screens\Pages;

use App\Models\Languages;
use App\Models\PageTranslations;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class Pages extends Screen
{
    /**
     * Display header name.
     *
     * @var string
     */
    public $name = 'Pages';

    /**
     * Display header description.
     *
     * @var string|null
     */
//    public $description = 'Pages';


    public $permission = [
        'platform.pages'
    ];
    /**
     * Query data.
     *
     * @return array
     */
    public function query(): array
    {
        $pages = \App\Models\Pages::filters()
            ->select('pages.id', 'page_translations.headline', 'languages.code as language', 'pages.code')
            ->leftJoin('page_translations', 'pages.id', '=', 'page_translations.page_id')
            ->leftJoin('languages', 'page_translations.language_id', '=',  'languages.id')
            ->orderBy('page_translations.title')
            ->groupBy('pages.code')
            ->paginate(20);

        return ['pages' => $pages];
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
                ->route('platform.pages.new')
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
            Layout::table('pages', [
                TD::make('headline', 'Headline')->sort(),
                TD::make('code', 'Code')->sort(),
                TD::make('language', 'Languages')->render(function ($target){
                    return Select::make("language[$target->id]")
                        ->fromModel(Languages::class, 'code', 'code')
                        ->value('en');
                })->sort(),
                TD::make('action', '')->render(function ($target) {
                    return DropDown::make()
                        ->icon('options-vertical')
                        ->class('btn sharp btn-primary tp-btn')
                        ->list([
                            Button::make('Edit')
                                ->method('edit')
                                ->parameters([
                                    'id' => $target->id
                                ])->rawClick()
                                ->class('dropdown-item')
                                ->icon('pencil'),
                        ]);
                })
            ]),
        ];
    }

    public function edit(Request $request){
       $input = $request->all();
       $language = $input['language'];
       $id = $input['id'];
//        dd(route('platform.pages.edit', ['id' => $id, 'lang' => $language[$id] ?? $language[0]]));
       return redirect()->route('platform.pages.edit', ['id' => $id, 'lang' => $language[$id] ?? $language[0]]);
    }
}
