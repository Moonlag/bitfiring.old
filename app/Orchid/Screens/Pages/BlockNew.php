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

class BlockNew extends Screen
{
    /**
     * Display header name.
     *
     * @var string
     */
    public $name = 'BlockNew';

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
                Input::make('block.code')->required()->title('Code'),
                Input::make('block.href')->required()->title('Link'),
                Input::make('block.url')->title('URL'),
                TextArea::make('block.description')->id('myTextarea')->spellcheck(),
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
        $input = $input['block'];

        $validator = Validator::make($input, [
            'href' => 'required',
            'code' => 'required',
        ]);

        if ($validator->fails()) {
            \Orchid\Support\Facades\Toast::error("Validation Error, " . array_keys($validator->failed())[0] . " ");
        } else {

            $page = \App\Models\Blocks::query()
                ->create([
                    'code' => $input['code'],
                    'published' => 1
                ]);

            \App\Models\BlockTranslations::query()
                ->create([
                    'block_id' => $page->id,
                    'language_id' => 1,
                    'active' => 1,
                    'description' => $input['description'],
                    'href' => $input['href'],
                    'code' => $input['code'],
                    'url' => $input['url'],
                ]);

            Alert::info('You have successfully.');

            return redirect()->route('platform.blocks');
        }
    }
}
