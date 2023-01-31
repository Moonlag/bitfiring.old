<?php

namespace App\Orchid\Screens\Pages;

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
use App\Http\Traits\EmailTrait;

use Orchid\Screen\Actions\ModalToggle;

class MailTemplatesEdit extends Screen
{
    use EmailTrait;
    /**
     * Display header name.
     *
     * @var string
     */
    public $name = 'MailEdit';
    public $description = 'MailEdit';
    public $mail_id;
    public $id;


    /**
     * Query data.
     *
     * @return array
     */
    public function query(\App\Models\MailTemplate $model, $lang): array
    {
        $language = Languages::query()->where('code', '=', $lang)->first();

        $mail_template = \App\Models\MailTemplateTranslations::query()->where('mail_id', $model->id)->where('language_id', $language->id)->first();

        if (!isset($mail_template->id)) {

            $mail_template = \App\Models\MailTemplateTranslations::firstOrCreate(
                [
                    'mail_id' => $model->id,
                    'language_id' => $language->id,
                ],
                [
                    'active' => 1,
                    'description' => '',
                    'template_name' =>  $model->template_name,
                ]);
        }

        $this->mail_id = $model->id;
        $this->id = $mail_template->id;
        $this->lang = $lang;

        return [
            'mail_template_translations' => $mail_template,
            'mail_template' => $model
        ];
    }

    /**
     * Button commands.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): array
    {
        return [

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
            Layout::rows([
                Group::make([
                    Select::make('lang')->fromModel(Languages::class, 'code', 'code')->value($this->lang)->title('Current language'),
                    Button::make('Change')->method('swap_language')->class('btn btn-primary btn-sm btn-block')->rawClick()
                ])->alignEnd(),
                Input::make('mail_template_translations.title')->required()->title('Title'),
                Input::make('mail_template_translations.template_name')->required()->title('Template Name'),
                TextArea::make('mail_template_translations.description')->id('myTextarea')->spellcheck(),
                Button::make('Update')->type(Color::SUCCESS())
                    ->rawClick()
                    ->method('update_code')->parameters([
                        'mail_template_translations_id' => $this->id,
                        'mail_template_id' => $this->mail_id
                    ]),
            ]),
            Layout::view('orchid.tinymce'),
            Layout::modal('TEST EMAIL', [
                Layout::rows([
                    Input::make('email')
                        ->type('email')
                        ->title('email')
                        ->required(),
                ]),
            ])->rawClick(),
        ];
    }

    public function update_code(Request $request)
    {
        $input = $request->all();

        \App\Models\MailTemplate::query()
            ->where('id', $input['mail_template_id'])
            ->update(['template_name' => $input['mail_template_translations']['template_name']]);

        \App\Models\MailTemplateTranslations::query()
            ->where('id', $input['mail_template_translations_id'])
            ->update([
                'description' => $input['mail_template_translations']['description'],
                'title' => $input['mail_template_translations']['title'],
                'template_name' => $input['mail_template_translations']['template_name'],
            ]);
        Alert::info('You have successfully.');
    }

    public function swap_language($id, Request $request){
        $input = $request->all();
        return redirect()->route('platform.mail-templates.edit', ['id' => $id, 'lang' => $input['lang']]);
    }
}
