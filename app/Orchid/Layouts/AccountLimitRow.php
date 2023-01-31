<?php

namespace App\Orchid\Layouts;

use Orchid\Screen\Field;
use Orchid\Screen\Layouts\Rows;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;

class AccountLimitRow extends Rows
{
    /**
     * Used to create the title of a group of form elements.
     *
     * @var string|null
     */
    protected $title;

    /**
     * Get the fields elements to be displayed.
     *
     * @return Field[]
     */
    protected function fields(): array
    {
        $limit = $this->query->getContent('limit_update');

        return [
            Select::make('limit_update.period_id')
                ->title('Period')
                ->fromQuery(\App\Models\LimitLink::leftJoin('limit_duration', 'limit_link.limit_duration_id', '=', 'limit_duration.period_id')
                    ->select('limit_link.limit_type_id', 'limit_duration.period_name', 'limit_duration.id')->where('limit_link.limit_type_id', $limit['type_id'] ?? 0), 'period_name', 'id')
                ->canSee($limit && $limit['type_id'] !== 3)
                ->required()
                ->empty('No select', '0'),

            Input::make('limit_update.amount')
                ->type('number')
                ->required()
                ->canSee($limit && $limit['type_id'] !== 2 && $limit['type_id'] !== 1)
                ->title($limit && $limit['type_id'] === 3 ? 'Minutes' :'USDT'),

        ];
    }
}
