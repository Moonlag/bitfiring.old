<?php

namespace App\Orchid\Screens\Finance;

use App\Models\Currency;
use App\Models\Partner;
use Illuminate\Support\Facades\DB;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;

class PaymentView extends Screen
{
    /**
     * Display header name.
     *
     * @var string
     */
    public $name = 'Payment';

    public $id;

    /**
     * Query data.
     *
     * @return array
     */
    public function query(\App\Models\Payments $model): array
    {
        $this->exist = $model->exists;
        if ($this->exist) {
            $this->description = 'id: ' . $model->id;
            $this->id = $model->id;
        }
       $partner = Partner::query()->where('id', $model->partner_id)->select('id', DB::Raw('IF(partners.company IS NULL, CONCAT(partners.firstname, " " ,partners.lastname), partners.company ) as partner'))->first();
        return [
            'overview' => [
                'basic_info' => [
                    'card_content' => [
                        'ID' => $model->id ?? '-',
                        'Partner' => Link::make($partner->partner)->route('platform.partners.view', $partner->id)->class('link-primary') ?? '-',
                        'Requested at' => $model->created_at ?? '-',
                        'Approved at' => $model->approved_at ?? '-',
                        'Finished at' => $model->finished_at ?? '-',
                        'Success' => $model->status === 2 ? 'YES' : 'NO',
                        'Amount' => $model->amount ?? '0.00',
                        'Commission Amount' => '0.00',
                        'Currency' => Currency::query()->where('id', $model->currency_id)->select('name')->first()->name ?? '-',
                    ]
                ],
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
            Layout::view('orchid.partners.overview.basic-info'),
        ];
    }
}
