<?php

namespace App\Orchid\Screens\Pages;

use App\Models\MailTextTranslations;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;

class Mail extends Screen
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

        $mail_template = MailTextTranslations::filters()
            ->select('mail_text_translations.id', 'mail_text.id as mail_id', 'mail_text_translations.code', 'languages.code as language', 'mail_text_translations.title')
            ->leftJoin('mail_text', 'mail_text_translations.mail_id', '=', 'mail_text.id')
            ->leftJoin('languages', 'mail_text_translations.language_id', '=', 'languages.id')
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
                ->route('platform.mail.new')
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
                TD::make('code', 'Code')->sort(),

                TD::make('action', '')->render(function (MailTextTranslations $model) {
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

        \App\Models\MailText::query()
            ->where('id', $input['mail_id'])
            ->delete();

        \App\Models\MailTextTranslations::query()
            ->where('id', $input['id'])
            ->delete();

        Alert::info('You have successfully.');
    }

    public function edit(Request $request){
        $input = $request->all();
        $language = $input['language'];
        $id = $input['id'];
//        dd(route('platform.pages.edit', ['id' => $id, 'lang' => $language[$id] ?? $language[0]]));
        return redirect()->route('platform.mail.edit', ['id' => $id, 'lang' => $language[$id] ?? $language[0]]);
    }
}
