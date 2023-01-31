<?php

namespace App\Orchid\Screens\Players;

use App\Models\Countries;
use App\Models\Currency;
use App\Models\GroupPlayers;
use App\Models\Groups;
use App\Models\Languages;
use App\Models\Tags;
use App\Orchid\Filters\DocumentFilter;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Fields\DateRange;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class Documents extends Screen
{
    /**
     * Display header name.
     *
     * @var string
     */
    public $name = 'Documents';

    /**
     * Display header description.
     *
     * @var string|null
     */
    public $description = 'Documents';

    /**
     * Query data.
     *
     * @return array
     */
    public function query(Request $request): array
    {
        $documents = \App\Models\Documents::filters()
            ->filtersApply([DocumentFilter::class])
            ->leftJoin('players', 'documents.player_id', '=', 'players.id')
            ->select('players.email as player', 'players.id as player_id', 'documents.created_at', 'documents.original_name', 'documents.description')->paginate();

        $count = \App\Models\Documents::query()->select('status')->get()->toArray();

        $groups = GroupPlayers::query()
            ->leftJoin('groups', 'group_players.group_id', '=', 'groups.id')
            ->select('group_players.user_id as id', 'groups.title', 'groups.color')->get()->toArray();

        foreach ($documents->items() as $item) {
            $item->group = array_values(array_filter($groups, function ($k, $v) use ($item) {
                return $item->player_id === $k['id'];
            }, 1));
        }
        return [
            'documents' => $documents,
            'filter' => [
                'title' => 'Filter',
                'group' => [
                    DateRange::make('created_at')
                        ->title('Created at')
                        ->value($request->created_at),

                    Select::make('status')
                        ->options([
                                0 => 'All',
                                1 => 'Approved',
                                2 => 'Seen',
                                3 => 'Reject',
                                4 => 'Removed',
                                5 => 'Pending',
                            ]
                        )
                        ->value((int)$request->status)
                        ->title('Status'),

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
            'scope' => [
                'title' => 'Scope Counters',
                'thead' => [
                    'Scope Name' => 'Count'
                ],
                'table' => [
                    'All' => count($count),
                    'Approved' => count(array_filter($count, function ($v, $k){
                        return $v['status'] === 1;
                    }, 1)),
                    'Seen' => count(array_filter($count, function ($v, $k){
                        return $v['status'] === 2;
                    }, 1)),
                    'Rejected' => count(array_filter($count, function ($v, $k){
                        return $v['status'] === 3;
                    }, 1)),
                    'Removed' => count(array_filter($count, function ($v, $k){
                        return $v['status'] === 4;
                    }, 1)),
                    'Pending' => count(array_filter($count, function ($v, $k){
                        return $v['status'] === 5;
                    }, 1)),
                ]
            ]
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
                        Layout::table('documents', [
                            TD::make('created_at', 'Created At')->render(function (\App\Models\Documents $model) {
                                return $model->created_at;
                            }),
                            TD::make('player', 'Player')->render(function (\App\Models\Documents $model) {
                                $link = Link::make($model->player)->class('link-primary')
                                    ->route('platform.players.profile', $model->player_id);
                                $description = $model->description;
                                $group = '';
                                foreach ($model->group as $kye){
                                    $color = $kye['color'];
                                    $title = $kye['title'];
                                    $group .= "<li style='background-color: $color;'><span style='border-color: transparent transparent transparent $color;'></span>$title</li>";
                                }
                                return "<div>$link
                                    <ul class='groups'>$group</ul>
                                    <span>$description</span>
                                </div>";
                            })->align(TD::ALIGN_LEFT),
                            TD::make('original_name', 'File')->align(TD::ALIGN_RIGHT),
                        ])

                    ],
                    'col_right' => [
                        Layout::view('orchid.players.scope-countres')
                    ]]
            )
        ];
    }

    public function clear_filter()
    {
        Toast::info(__('Filter cleared'));
        return redirect()->route('platform.players.documents');
    }

    public function apply_filter(Request $request)
    {
        Toast::success(__('Filter apply'));
        return redirect()->route('platform.players.documents', array_filter($request->all(), function ($k, $v) {
            return $k != '0' && $v != '_token';
        }, ARRAY_FILTER_USE_BOTH));
    }
}
