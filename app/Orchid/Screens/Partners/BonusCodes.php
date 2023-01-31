<?php

namespace App\Orchid\Screens\Partners;

use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Screen;
use Orchid\Support\Color;


class BonusCodes extends Screen
{
    /**
     * Display header name.
     *
     * @var string
     */
    public $name = 'Bonus Codes';

    /**
     * Display header description.
     *
     * @var string
     */
//    public $description = 'BonusCodes';

    /**
     * Permissions for this screen
     *
     * @var array|string
     */
    public $permission = [
        'platform.partners.bonus-codes'
    ];

    /**
     * Query data.
     *
     * @return array
     */
    public function query(): array
    {
        return [
            'text' => 'No bonus codes yet',
            'filter' => [
                'group' => [
                    Input::make('bonus_code')
                        ->type('text')
                        ->title('Bonus code')
                        ->placeholder('Bonus code')
                        ->class('form-control mw-100'),

                    Select::make('brand')
                        ->options([
                            'all' => 'All',
                            'noindex' => 'No index',

                        ])
                        ->title('Balance currency'),

                    Input::make('partner_id')
                        ->type('email')
                        ->title('Partner ID')
                        ->placeholder('Partner ID')
                        ->class('form-control mw-100'),

                    Button::make('Filter')
                        ->vertical()
                        ->class('btn btn-outline-info')
                        ->method('apply_filter'),

                    Button::make('Clear')
                        ->method('clear_filter')
                        ->class('btn btn-default')
                        ->vertical()

                ],
                'title' => 'Filter'
            ],
            'navTable' => [
                'group' => [
                    Button::make('Export to CVS')
                        ->class('btn btn-info'),
                ],
                'title' => 'Bonus codes'
            ]
        ];
    }

    /**
     * Button commands.
     *
     * @return Action[]
     */
    public function commandBar(): array
    {
        return [
            Button::make('Active')
                ->icon('docs')
                ->class('btn btn-info p-3'),
            Button::make('Archived')
                ->icon('docs')
                ->class('btn disabled  p-3'),
            Button::make('New')
                ->icon('plus')
                ->class('btn btn-success ml-2  p-3'),
        ];

    }

    /**
     * Views.
     *
     * @return Layout[]
     */
    public function layout(): array
    {
        return [
            Layout::wrapper('admin.mainWrapper', [
                'col_left' =>
                    Layout::view('admin.filter'),
                'col_right' => [
                    Layout::view('admin.navTable'),
                ]]),
        ];
    }
}
