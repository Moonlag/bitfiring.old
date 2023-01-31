<?php

namespace App\Orchid\Screens\Marketing;

use Carbon\Carbon;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;

class ChurnRateReport extends Screen
{
    /**
     * Display header name.
     *
     * @var string
     */
    public $name = 'ChurnRateReport';

    /**
     * Display header description.
     *
     * @var string|null
     */
    public $description = 'ChurnRateReport';

    public $permission = [
        'platform.finance.churn-rate-report'
    ];


    /**
     * Query data.
     *
     * @return array
     */
    public function query(): array
    {
        $date_count = 0;
        $now = Carbon::now()->startOfMonth();
        $data = [];
        $table_head = [];
        while ($date_count <= 12){
            $data[] = [
                'date' => $now->format('F Y')
            ];
            $table_head [] = [
                'date' => $now->format('F Y')
            ];
            $now->subMonth();
            $date_count++;
        }

        return [
            'data' => $data,
            'thead' => $data,
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
            Layout::view('orchid.marketing.churn-rate')
        ];
    }
}
