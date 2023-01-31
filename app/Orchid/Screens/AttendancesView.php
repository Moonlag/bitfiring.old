<?php

namespace App\Orchid\Screens;

use App\Models\Players;
use App\Models\User;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;

class AttendancesView extends Screen
{
    /**
     * Display header name.
     *
     * @var string
     */
    public $name = 'Attendance';

    /**
     * Display header description.
     *
     * @var string|null
     */
    public $description = 'AttendancesView';

    public $permission = [
        'platform.attendances'
    ];
    /**
     * Query data.
     *
     * @return array
     */
    public function query(\App\Models\Attendances $model): array
    {
        $this->exist = $model->exists;
        if ($this->exist) {
            $this->description = $model->id;
        }
        $admin = User::query()->where('id', $model->staff_id)->select('email', 'id')->first();

        return [
            'info' => [
                'title' => 'Attendance Details',
                'table' => [
                    'Admin User' => $admin->email ?? '-',
                    'Session sID' =>  $model->session_sid,
                    'Path' => $model->path ?? '-',
                    'HTTP Method' => $model->http_method ?? '-',
                    'Params' => $model->params ?? '{}',
                    'User agent' => $model->user_agent ?? '-',
                    'Referer' => $model->referer ?? '-',
                    'Created at' => $model->created_at ?? '-',
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
                ->class('btn btn-outline-secondary mb-2')
                ->icon('left')
                ->route('platform.attendances'),
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
            Layout::view('orchid.info')
        ];
    }
}
