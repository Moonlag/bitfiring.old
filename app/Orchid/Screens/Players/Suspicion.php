<?php

namespace App\Orchid\Screens\Players;

use App\Models\Sessions;
use App\Models\Suspicions;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class Suspicion extends Screen
{
    /**
     * Display header name.
     *
     * @var string
     */
    public $name = 'Suspicion';

    protected $id;
    /**
     * Display header description.
     *
     * @var string|null
     */
    public $description = 'Suspicion';

    public $permission = [
        'platform.players.suspicion'
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

        $session = Sessions::query()->where('user_id', '=', $model->id)
            ->get();

        if(!!$session){
            $session = Sessions::query()
                ->where('user_id', '!=', $model->id)
                ->whereIn('ip', $session->pluck('ip'))
                ->orWhere('user_id', '!=', $model->id)
                ->whereIn('user_agent', $session->pluck('user_agent'))
                ->whereIn('platform', $session->pluck('platform'))
                ->whereIn('viewport', $session->pluck('viewport'))
                ->select('user_id')
                ->get()
                ->pluck('user_id');


            $suspicions = Suspicions::query()
                ->whereIn('user_id', $session)
                ->Join('players', 'suspicions.user_id', '=', 'players.id')
                ->leftJoin('suspicion_types', 'suspicions.reason_id', '=', 'suspicion_types.id')
                ->select('suspicions.created_at', 'suspicions.updated_at', 'suspicion_types.suspicion_name as reason', 'players.id as player_id')
                ->paginate(10);
        }


        return [
            'suspicions' => $suspicions ?? []
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
            Layout::table('suspicions', [
                TD::make('suspect', 'Suspect')->render(function (Suspicions $model) {
                    $model = \App\Models\Players::find($model->player_id);
                    $link = Link::make($model->email)->class('link-primary')
                        ->route('platform.players.profile', $model->id);
                    $groups = '';
                    foreach ($model->groups as $group) {
                        $color = $group->color;
                        $title = $group->title;
                        $groups .= "<li style='background-color: $color;'>$title</li>";
                    }
                    return "<div>$link
                                    <ul class='groups'>$groups</ul>
                                </div>";
                })->sort(),
                TD::make('reason', 'Reason')->sort(),
                TD::make('created_at', 'Created at')
                    ->render(function (Suspicions $model) {
                        return $model->created_at;
                    })->sort(),
                TD::make('updated_at', 'Updated at')
                    ->render(function (Suspicions $model) {
                        return $model->updated_at;
                    })->sort(),
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
