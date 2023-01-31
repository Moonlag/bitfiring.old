<?php

namespace App\Orchid\Screens\Pages;

use App\Models\Languages;
use App\Models\PageTranslations;
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

class MailEdit extends Screen
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
    public function query(\App\Models\MailText $model, $lang): array
    {

        $this->id = $model->id;

        $language = Languages::query()->where('code', '=', $lang)->first();

        $mail_template = \App\Models\MailTextTranslations::query()->where('mail_id', $model->id)->where('language_id', $language->id)->first();

        if (!isset($mail_template->id)) {
            $mail_template = \App\Models\MailTextTranslations::query()->where('mail_id', $model->id)->first();

            $mail_template = \App\Models\MailTextTranslations::firstOrCreate(
                [
                    'mail_id' => $model->id,
                    'language_id' => $language->id,
                ],
                [
                    'active' => 1,
                    'description' => '',
                    'code' =>  $model->code,
                    'template_id' =>  $mail_template->template_id,
                ]);
        }

        $this->mail_id = $model->id;
        $this->id = $mail_template->id;
        $this->lang = $lang;
        return [
            'mail_text_translations' => $mail_template,
            'mail_text' => $model
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
            ModalToggle::make('TEST EMAIL')
                ->modal('TEST EMAIL')
                ->method('test_mail')
                ->parameters(['id' => $this->id])
                ->class('btn btn-secondary mb-2'),
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
                Input::make('mail_text_translations.title')->required()->title('Title'),
                Input::make('mail_text_translations.code')->required()->title('Code'),
                Select::make('mail_text_translations.template_id')->title('Template Name')->fromModel(\App\Models\MailTemplateTranslations::class, 'title'),
                TextArea::make('mail_text_translations.description')->id('myTextarea')->spellcheck(),
                Button::make('Update')->type(Color::SUCCESS())
                    ->rawClick()
                    ->method('update_code')->parameters([
                        'mail_text_translations_id' => $this->id,
                        'mail_text_id' => $this->mail_id
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

        \App\Models\MailText::query()
            ->where('id', $input['mail_text_id'])
            ->update(['code' => $input['mail_text_translations']['code']]);

        \App\Models\MailTextTranslations::query()
            ->where('id', $input['mail_text_translations_id'])
            ->update([
                'description' => $input['mail_text_translations']['description'],
                'title' => $input['mail_text_translations']['title'],
                'code' => $input['mail_text_translations']['code'],
                'template_id' => $input['mail_text_translations']['template_id'],
            ]);
        Alert::info('You have successfully.');
    }

    public function test_mail(Request $request, \App\Models\MailTextTranslations $model){
        $input = $request->all();
        $this->send_email($model->code, $input['email'], [], true);
        Alert::info('You have successfully.');
        return redirect()->back();
    }

    public function swap_language($id, Request $request){
        $input = $request->all();
        return redirect()->route('platform.mail.edit', ['id' => $id, 'lang' => $input['lang']]);
    }
}
