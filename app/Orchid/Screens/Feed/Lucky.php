<?php

namespace App\Orchid\Screens\Feed;

use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Fields\DateRange;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use Orchid\Support\Facades\Toast;

class Lucky extends Screen
{
    /**
     * Display header name.
     *
     * @var string
     */
    public $name = 'Lucky';

    /**
     * Query data.
     *
     * @return array
     */
    public function query(Request $request): array
    {
        $current_page = $request->get('page', 1);

        $query = ['page' => $current_page];

        if ($request->has('players')) {
            $player_email = $request->get('players', false);
            $players = \App\Models\Players::query()->where('email', 'LIKE', "%$player_email%")->pluck('id');
            $query['players_ids'] = $players->toArray();
        }

        if ($request->has('rewards')) {
            $query['rewards_ids'] = $request->get('rewards', []);
        }

        if ($request->has('spinned_at.start') && $request->has('spinned_at.end')) {
            $spinned_at = $request->get('spinned_at');
            $query['spinned_at'] = [$spinned_at['start'], $spinned_at['end']];
        }

        $response = Http::get('https://bitfiring.back-dev.com/lucky/public/api/spins', $query);
        $spins = $response->collect();

        $response = Http::get('https://bitfiring.back-dev.com/lucky/public/api/reward');
        $rewards = $response->collect('data');
        return [
            'table' => $spins['data'],
            'meta' => $spins['meta'],
            'filter' => [
                'group' => [
                    DateRange::make('spinned_at')
                        ->title('Spinned at')
                        ->value($request->spinned_at),

                    Input::make('players')
                        ->type('text')
                        ->title('Player:')
                        ->value($request->players),

                    Select::make('rewards[]')
                        ->options(
                            $rewards->mapWithKeys(function ($item, $key) {
                                return [$item['id'] => $item['winn']];
                            })
                        )
                        ->multiple()
                        ->value($request->rewards ? array_map(function ($item) {
                            return (int)$item;
                        }, $request->rewards) : '')
                        ->title('Rewards:'),
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
                    Layout::view('orchid.table.lucky')
                ],
            ])
        ];
    }

    public function clear_filter()
    {
        Toast::info(__('Filter cleared'));
        return redirect()->route('platform.feed.lucky');
    }

    public function apply_filter(Request $request)
    {
        Toast::success(__('Filter apply'));

        return redirect()->route('platform.feed.lucky', array_filter($request->all(), function ($k, $v) {
            return $k != '0' && $v != '_token';
        }, ARRAY_FILTER_USE_BOTH));
    }
}
