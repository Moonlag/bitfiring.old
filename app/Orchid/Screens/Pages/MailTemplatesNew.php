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

class MailTemplatesNew extends Screen
{
    /**
     * Display header name.
     *
     * @var string
     */
    public $name = 'MailNew';

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
                Input::make('mail.title')->required()->title('Title'),
                Input::make('mail.template_name')->required()->title('Template Name'),
                TextArea::make('mail.description')->id('myTextarea')->spellcheck(),
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
        $input = $input['mail'];

        $validator = Validator::make($input, [
            'title' => 'required',
            'template_name' => 'required',
        ]);

        if ($validator->fails()) {
            \Orchid\Support\Facades\Toast::error("Validation Error, " . array_keys($validator->failed())[0] . " ");
        } else {

            $page = \App\Models\MailTemplate::query()
                ->create([
                    'template_name' => $input['template_name'],
                    'published' => 1
                ]);

            \App\Models\MailTemplateTranslations::query()
                ->create([
                    'mail_id' => $page->id,
                    'language_id' => 1,
                    'active' => 1,
                    'description' => $input['description'],
                    'template_name' => $input['template_name'],
                    'title' => $input['title'],
                ]);

            Alert::info('You have successfully.');

            return redirect()->route('platform.mail-templates');
        }
    }
}
