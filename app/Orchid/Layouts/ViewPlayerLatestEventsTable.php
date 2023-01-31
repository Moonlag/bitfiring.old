<?php

namespace App\Orchid\Layouts;

use Orchid\Screen\Actions\Link;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class ViewPlayerLatestEventsTable extends Table
{
    /**
     * Data source.
     *
     * The name of the key to fetch it from the query.
     * The results of which will be elements of the table.
     *
     * @var string
     */
    protected $target = 'latest_events';

    protected $title = 'Latest events';
    /**
     * @return string
     */
    protected function textNotFound(): string
    {
        return __('No data found');
    }


    /**
     * @return string
     */
    protected function iconNotFound(): string
    {
        return 'table';
    }

    /**
     * Enable a hover state on table rows.
     *
     * @return bool
     */
    protected function hoverable(): bool
    {
        return true;
    }

    protected function striped(): bool
    {
        return true;
    }

    protected function action()
    {
        return [Link::make('All events')
            ->route('platform.players.events', $this->query->get('player')->id)
            ->icon('link')->class('btn btn-rounded btn-outline-primary btn-sm')];
    }

    /**
     * Get the table cells to be displayed.
     *
     * @return TD[]
     */
    protected function columns(): array
    {
        return [
                TD::make('created_at', 'Date')->render(function ($latest_events){
                    return $latest_events->created_at ?? '-';
                })->sort(),
                TD::make('subject', 'Subject')->sort(),
                TD::make('event_type', 'Event type')->sort(),
                TD::make('ip', 'IP')->sort(),
                TD::make('country', 'Country')->sort(),
                TD::make('address', 'Address')->sort(),
                TD::make('coordinates', 'Coordinates')->sort(),
        ];
    }

}
