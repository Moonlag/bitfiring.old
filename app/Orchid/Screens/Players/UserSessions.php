<?php

namespace App\Orchid\Screens\Players;

use App\Models\Sessions;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class UserSessions extends Screen
{
    /**
     * Display header name.
     *
     * @var string
     */
    public $name = 'UserSessions';

    protected $id;
    /**
     * Display header description.
     *
     * @var string|null
     */
    public $description = 'UserSessions';


    public $permission = [
        'platform.players.session'
    ];
    /**
     * Query data.
     *
     * @return array
     */
    public function query(\App\Models\Players $model): array
    {
        $this->exist = $model->exists;
        if ($this->exist) {
            $this->name = $model->email;
            $this->description = 'id: ' . $model->id;
            $this->id = $model->id;
        }

        $sessions = Sessions::where('user_id', '=', $model->id)
            ->select('sessions.user_agent', 'sessions.device_type', 'sessions.device',
                'sessions.ip', 'sessions.created_at', 'sessions.country')
            ->leftJoin('countries', 'sessions.country', '=', 'countries.id')
            ->orderBy('sessions.id', 'DESC')
            ->paginate(20);

        return [
            'sessions' => $sessions,
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
            ->class('btn btn-outline-secondary mb-2')
            ->icon('left')
            ->route('platform.players.profile', $this->id)
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
            Layout::table('sessions', [
                TD::make('email', 'User')
                    ->render(function () {
                        return $this->name;
                    })->sort(),
                TD::make('created_at', 'Date')
                    ->render(function (Sessions $model) {
                        return $model->created_at;
                    })->sort(),
                TD::make('user_agent', 'User agent')
                    ->width(500)->sort(),
                TD::make('device', 'Device')
                    ->width(500)->sort(),
                TD::make('device_type', 'Device type')->render(function (Sessions $model){
                    switch ($model->device_type){
                        case 1:
                            return 'D';
                        case 2:
                            return 'M';
                        case 3:
                            return 'DM';
                    }
                })->sort(),
                TD::make('ip', 'IP')->sort(),
                TD::make('country', 'Country')->sort(),
                TD::make('action', '')->render(function () {
                    return DropDown::make()
                        ->icon('options-vertical')
                        ->list([
                            Button::make(__('view'))->class('dropdown-item disabled')
                                ->confirm(__('Are you sure you want to change status state?')),
                        ]);
                })
            ]),
        ];
    }
}
