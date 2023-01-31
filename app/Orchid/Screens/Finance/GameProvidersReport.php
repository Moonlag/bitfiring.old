<?php

namespace App\Orchid\Screens\Finance;

use App\Models\Currency;
use App\Models\FeedExports;
use App\Models\GamesBets;
use App\Models\GamesProvider;
use App\Models\Transactions;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\DateRange;
use Orchid\Screen\Fields\CheckBox;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class GameProvidersReport extends Screen
{
    /**
     * Display header name.
     *
     * @var string
     */
    public $name = 'GameProvidersReport';

    /**
     * Display header description.
     *
     * @var string|null
     */
    public $description = 'GameProvidersReport';

    public $permission = [
        'platform.finance.game-providers-report'
    ];

    public $staff;
    public $export = 'game-providers-report';

    /**
     * Query data.
     *
     * @return array
     */
    public function query(Request $request): array
    {

        $this->staff = $request->user();

        $request->query->set('date_range', [
            'start' => request('date_range.start', Carbon::now()->startOfMonth()->format('Y-m-d')),
            'end' => request('date_range.end', Carbon::now()->format('Y-m-d'))
        ]);

        $provider = GamesProvider::all();


        $provider = $provider->map(function ($item) {
            $bets = $item->bets()->whereBetween('games_bets.created_at', [request('date_range.start'), request('date_range.end')])->get();

            return [
                'title' =>  $item->title,
                'date' => request('date_range.start') . ' / ' . request('date_range.end'),
                'average_bet' => $bets->count() ? number_format($bets->sum('bet_sum') / $bets->count() , 2, '.', ' '): 0,
                'percent' => $item->fee,
                'ggr' => number_format($bets->sum('bet_sum') - $bets->sum('profit'), 2, '.',' '),
            ];
        });

        return [
            'provider' => $provider
            ,
            'filter' => [
                'group' => [
                    DateRange::make('date_range')
                        ->title('Date Range')
                        ->value($request->date_range),

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
            Button::make('Export')
                ->rawClick()
                ->method('add_export')
                ->parameters(
                    [
                        'export' => [
                            'type_name' => $this->export,
                            'staff_id' => $this->staff->id,
                            'status' => 1,
                        ]
                    ]
                )
                ->icon('share-alt')
                ->class('btn btn-outline-secondary'),
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
                    Layout::view('orchid.filter'),
                    Layout::view('orchid.finance.game-provide')
                ],
            ]),
        ];
    }

    public function clear_filter()
    {
        Toast::info(__('Filter cleared'));
        return redirect()->route('platform.finance.game-providers-report');
    }

    public function apply_filter(Request $request)
    {
        Toast::success(__('Filter apply'));
        return redirect()->route('platform.finance.game-providers-report', array_filter($request->all(), function ($k, $v) {
            return $k != '0' && $v != '_token';
        }, ARRAY_FILTER_USE_BOTH));
    }

    public function add_export(Request $request)
    {
        $fileName = 'game-providers-report-' . Carbon::now()->format('Y-m-d') . '.csv';

        $tasks = GamesProvider::all();

        $tasks = $tasks->map(function ($item) {
            $bets = $item->bets()->whereBetween('games_bets.created_at', [request('date_range.start'), request('date_range.end')])->get();

            return [
                'title' =>  $item->title,
                'date' => request('date_range.start') . ' / ' . request('date_range.end'),
                'average_bet' => $bets->count() ? number_format($bets->sum('bet_sum') / $bets->count() , 2, '.', ' '): 0,
                'percent' => $item->fee,
                'ggr' => number_format($bets->sum('bet_sum') - $bets->sum('profit'), 2, '.',' '),
            ];
        });

        $header = $headers = [
            'Content-type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=$fileName"
        ];


        $columns = array('Title', 'Date', 'Average Bet', 'Percent', 'GGR');


        $callback = function () use ($tasks, $columns) {
            $stream = fopen('php://output', 'w');
            fputcsv($stream, $columns);

            foreach ($tasks as $task) {
                fputcsv($stream, [
                    'Title' => $task['title'],
                    'Date' => $task['date'],
                    'Average Bet' => $task['average_bet'],
                    'Percent' => $task['percent'],
                    'GGR' => $task['ggr'],
                ]);

            }
            fclose($stream);
        };

//        $data = array_merge($request->export, ['url' => $fileName]);
//        FeedExports::query()->insert($data);
        Toast::info(__('Success'));
        return response()->stream($callback, 200, $headers);
    }
}
