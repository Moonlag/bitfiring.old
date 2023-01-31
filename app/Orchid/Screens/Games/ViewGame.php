<?php

namespace App\Orchid\Screens\Games;

use App\Models\Countries;
use App\Models\Currency;
use App\Models\Groups;
use App\Models\Languages;
use App\Models\Players;
use App\Models\Tags;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Fields\DateRange;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;

class ViewGame extends Screen
{
    /**
     * Display header name.
     *
     * @var string
     */
    public $name = 'ViewGame';

    /**
     * Display header description.
     *
     * @var string|null
     */
    public $description = 'ViewGame';


    public $permission = [
        'platform.games.view'
    ];

    /**
     * Query data.
     *
     * @return array
     */
    public function query(\App\Models\Games $model, Request $request): array
    {
        $this->exist = $model->exists;
        if ($this->exist) {
            $this->name = $model->name;
            $this->description = $model->id;
        }

        return [
            'filter' => [
                'title' => 'Changes',
                'group' => [
                    Button::make('Changes History')
                        ->icon('refresh')
                        ->method('clear_filter')
                        ->class('btn btn-default btn-block')
                        ->vertical(),
                ],
            ],
            'info' => [
                'title' => 'Bitcoin Address Details',
                'table' => [
                    'ID' => $model->id,
                    'Provider' => $model->provider,
                    'Identer' => $model->identer,
                    'Created at' => $model->created_at,
                    'Updated at' => $model->updated_at,
                ]
            ],
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
            Link::make('Return')
                ->icon('left')
                ->class('btn btn-outline-secondary mb-2')
                ->route('platform.games')
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
            Layout::wrapper('orchid.wrapper-col2', [
                    'col_left' => [
                        Layout::view('orchid.info'),
                        Layout::view('orchid.filter'),
                    ],]
            )
        ];
    }
}
