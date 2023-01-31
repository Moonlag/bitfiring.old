<?php

namespace App\Orchid\Screens\Pages;

use App\Models\Languages;
use App\Models\MailTemplateTranslations;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;

class MailTemplates extends Screen
{
    /**
     * Display header name.
     *
     * @var string
     */
    public $name = 'Mail';

    /**
     * Query data.
     *
     * @return array
     */
    public function query(): array
    {


        $mail_template = MailTemplateTranslations::filters()
            ->select('mail_template_translations.id', 'mail_template.id as mail_id', 'mail_template_translations.template_name', 'languages.code as language', 'mail_template_translations.title')
            ->leftJoin('mail_template', 'mail_template_translations.mail_id', '=', 'mail_template.id')
            ->leftJoin('languages', 'mail_template_translations.language_id', '=', 'languages.id')
            ->paginate(20);

        return ['mail_template' => $mail_template];
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
                ->route('platform.mail-templates.new')
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
            Layout::table('mail_template', [
                TD::make('title', 'Title')->sort(),
                TD::make('template_name', 'Template name')->sort(),
                TD::make('language', 'Languages')->render(function ($target){
                    return Select::make("language[$target->id]")
                        ->fromModel(Languages::class, 'code', 'code')
                        ->value('en');
                })->sort(),
                TD::make('action', '')->render(function (MailTemplateTranslations $model) {
                    return DropDown::make()
                        ->icon('options-vertical')
                        ->class('btn sharp btn-primary tp-btn')
                        ->list([
                            Button::make('Edit')
                                ->method('edit')
                                ->parameters([
                                    'id' => $model->id
                                ])->rawClick()
                                ->class('dropdown-item')
                                ->icon('pencil'),
                            Button::make('Delete')
                                ->icon('trash')
                                ->method('mail_delete')
                                ->class('dropdown-item')
                                ->parameters([
                                    'id' => $model->id,
                                    'mail_id' => $model->mail_id
                                ])
                        ]);
                })
            ]),
        ];
    }

    public function mail_delete(Request $request)
    {
        $input = $request->all();

        \App\Models\MailTemplate::query()
            ->where('id', $input['mail_id'])
            ->delete();

        \App\Models\MailTemplateTranslations::query()
            ->where('id', $input['id'])
            ->delete();

        Alert::info('You have successfully.');
    }

    public function edit(Request $request){
        $input = $request->all();
        $language = $input['language'];
        $id = $input['id'];
//        dd(route('platform.pages.edit', ['id' => $id, 'lang' => $language[$id] ?? $language[0]]));
        return redirect()->route('platform.mail-templates.edit', ['id' => $id, 'lang' => $language[$id] ?? $language[0]]);
    }
}
