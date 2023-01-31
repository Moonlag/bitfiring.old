<?php

namespace App\Orchid\Screens\Pages;

use App\Models\BlockTranslations;
use App\Models\Languages;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Screen;
use Orchid\Support\Color;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;

class BlockEdit extends Screen
{
    /**
     * Display header name.
     *
     * @var string
     */
    public $name = 'BlockEdit';
    public $description = 'blocksEdit';
    public $block_id;
    public $id;
    public $request;


    public function __construct(Request $request)
    {
        $this->request = $request;
    }


    /**
     * Query data.
     *
     * @return array
     */
    public function query(\App\Models\Blocks $model, $lang): array
    {
        $language = Languages::query()->where('code', '=', $lang)->first();
        $block_translation = BlockTranslations::query()->where('block_id', '=', $model->id)->where('language_id', $language->id)->first();
        $default = BlockTranslations::query()->where('block_id', '=', $model->id)->first();
        $this->id = $model->id;

        if (!isset($block_translation->id)) {
            $block_translation = BlockTranslations::firstOrCreate(
                [
                    'block_id' => $model->id,
                    'language_id' => $language->id,
                ],
                [
                    'active' => 1,
                    'description' => '',
                    'code' => $model->code,
                    'href' => $default->href,
                ]
            );
        }

        $this->id = $block_translation->id;
        $this->block_id = $model->id;
        $this->lang = $lang;

        return [
            'block_translations' => $block_translation,
            'blocks' => $model
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
                Input::make('blocks.code')->required()->title('Code'),
                Input::make('block_translations.href')->required()->title('Link'),
                Input::make('block_translations.url')->title('URL'),
                TextArea::make('block_translations.description')->id('myTextarea')->spellcheck(),
                Button::make('Update')->type(Color::SUCCESS())
                    ->rawClick()
                    ->method('update_code')->parameters([
                        'block_translations_id' => $this->id,
                        'blocks_id' => $this->block_id
                    ])
            ]),
        ];
    }

    public function update_code(Request $request)
    {
        $input = $request->all();

        \App\Models\Blocks::query()
            ->where('id', $input['blocks_id'])
            ->update(['code' => $input['blocks']['code']]);

        \App\Models\BlockTranslations::query()
            ->where('id', $input['block_translations_id'])
            ->update([
                'description' => $input['block_translations']['description'],
                'href' => $input['block_translations']['href'],
                'url' => $input['block_translations']['url'],
            ]);
        Alert::info('You have successfully.');
    }

    public function swap_language($id, Request $request){
        $input = $request->all();
        return redirect()->route('platform.block.edit', ['id' => $id, 'lang' => $input['lang']]);
    }
}
