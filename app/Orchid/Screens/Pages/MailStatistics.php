<?php

namespace App\Orchid\Screens\Pages;

use Orchid\Screen\Screen;
use Illuminate\Http\Request;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;
use Orchid\Screen\TD;
use App\Orchid\Filters\MailStatisticsFilter;

class MailStatistics extends Screen
{
    /**
     * Display header name.
     *
     * @var string
     */
    public $name = 'Mail Statistics';

    /**
     * Query data.
     *
     * @return array
     */
    public function query(Request $request): array
    {
        $table = \App\Models\MailStatistics::query()
            ->filtersApply([MailStatisticsFilter::class])
            ->select('mail_statistics.*', 'mail_text_translations.title as mail_title')
            ->Join('mail_text_translations', 'mail_statistics.mail_text_id', '=', 'mail_text_translations.id')
            ->orderBy('mail_statistics.id', 'DESC')
            ->paginate(100);

        return [
            'filter' => [
                'group' => [
                    Input::make('email')
                        ->title('Email')
                        ->value($request->email),
                ],
                'action' => [
                    Button::make('Filter')
                        ->vertical()
                        ->icon('filter')
                        ->class('btn btn-primary btn-sm btn-block')
                        ->method('apply_filter'),

                    Button::make('Clear')
                        ->icon('refresh')
                        ->method('clear_filter')
                        ->class('btn btn-sm btn-dark')
                        ->vertical(),
                ]
            ],
            'mails' => $table
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
            Layout::wrapper('orchid.wrapper-col2', [
                'col_left' => [
                    Layout::view('orchid.filter'),
                    Layout::table('mails', [
                        TD::make('id', 'ID')->sort(),
                        TD::make('email', 'Email')->render(function (\App\Models\MailStatistics $model){
                            return Link::make($model->email)->route('platform.mail-statistics', ['email' => $model->email])->class('link-primary');
                        })
                            ->sort(),
                        TD::make('mail_title', 'Mail title')
                            ->sort(),
                        TD::make('created_at', 'date')
                            ->render(function(\App\Models\MailStatistics $model){
                                return $model->created_at;
                            })
                            ->sort(),
                    ]),
                ]])
        ];
    }

    public function clear_filter()
    {
        Toast::info(__('Filter cleared'));
        return redirect()->route('platform.mail-statistics');
    }

    public function apply_filter(Request $request)
    {
        Toast::success(__('Filter apply'));
        return redirect()->route('platform.mail-statistics', array_filter($request->all(), function ($k, $v) {
            return $k != '0' && $v != '_token';
        }, ARRAY_FILTER_USE_BOTH));
    }
}
