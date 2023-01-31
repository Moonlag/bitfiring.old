<?php

namespace App\Orchid\Screens\Pages;

use App\Models\BlockTranslations;
use App\Models\Languages;
use App\Models\PageTranslations;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;

class Blocks extends Screen
{
    /**
     * Display header name.
     *
     * @var string
     */
    public $name = 'Blocks';

    /**
     * Query data.
     *
     * @return array
     */
    public function query(): array
    {
        $blocks = BlockTranslations::filters()
            ->select('blocks.id', 'blocks.id as block_id', 'block_translations.href', 'languages.code as language', 'blocks.code')
            ->leftJoin('blocks', 'block_translations.block_id', '=', 'blocks.id')
            ->leftJoin('languages', 'block_translations.language_id', '=', 'languages.id')
            ->groupBy('blocks.code')
            ->paginate(20);

        return ['blocks' => $blocks];
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
                ->route('platform.block.new')
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
            Layout::table('blocks', [
                TD::make('code', 'Code')->sort(),
                TD::make('href', 'Link')->sort(),
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
                            Button::make('Delete')
                                ->icon('trash')
                                ->method('block_delete')
                                ->class('dropdown-item')
                                ->parameters([
                                    'id' => $target->id,
                                    'block_id' => $target->block_id
                                ])
                        ]);
                })
            ]),
        ];
    }

    public function block_delete(Request $request)
    {
        $input = $request->all();

        \App\Models\Blocks::query()
            ->where('id', $input['block_id'])
            ->delete();

        \App\Models\BlockTranslations::query()
            ->where('id', $input['id'])
            ->delete();

        Alert::info('You have successfully.');
    }

    public function edit(Request $request){
        $input = $request->all();
        $language = $input['language'];
        $id = $input['id'];
//        dd(route('platform.pages.edit', ['id' => $id, 'lang' => $language[$id] ?? $language[0]]));
        return redirect()->route('platform.block.edit', ['id' => $id, 'lang' => $language[$id] ?? $language[0]]);
    }
}
