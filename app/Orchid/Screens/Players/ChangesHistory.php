<?php

namespace App\Orchid\Screens\Players;

use App\Models\Changes;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class ChangesHistory extends Screen
{
    /**
     * Display header name.
     *
     * @var string
     */
    public $name = 'Changes History';
    private $exist = false;
    public $id;
    /**
     * Display header description.
     *
     * @var string|null
     */
    public $description = 'Changes History';

    /**
     * Query data.
     *
     * @return array
     */
    public function query(\App\Models\Players $model, Request $request): array
    {
        $this->exist = $model->exists;
        if ($this->exist) {
            $this->name = $model->email;
            $this->id = $model->id;
        }

        $players = Changes::query()
            ->leftJoin('players', 'changes.user_id', '=', 'players.id')
            ->where('changes.user_id', $model->id)
            ->whereNull('players.deleted_at')
            ->select('players.email', 'changes.id as id', 'changes.created_at', 'changes.request_name', 'changes.request')
            ->paginate();

        return [
            'table' => $players
        ];
    }

    /**
     * Button commands.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): array
    {
        return [Link::make('Return')
            ->class('btn btn-outline-secondary mb-2')
            ->icon('left')
            ->route('platform.players.profile', $this->id)];
    }

    /**
     * Views.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    public function layout(): array
    {
        return [
            Layout::table('table', [
                TD::make('id', 'ID')->render(function (Changes $model){
                    return $model->id;
                })->sort(),
                TD::make('email')->render(function (Changes $model){
                    return "<div class='d-flex flex-column'><span>Player: $model->email</span><span>$model->created_at</span></div>";
                })->sort(),
                TD::make('request_name', 'Request Name')->render(function (Changes $model){
                    return $model->request_name ?? '-';
                })->sort(),
                TD::make('request')->render(function (Changes $model){
                    return $model->request ?? '-';
                })->sort(),
            ])
        ];
    }
}
