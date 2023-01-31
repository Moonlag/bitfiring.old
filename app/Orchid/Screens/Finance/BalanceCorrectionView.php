<?php

namespace App\Orchid\Screens\Finance;

use App\Models\Brands;
use App\Models\Currency;
use App\Models\Partner;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;

class BalanceCorrectionView extends Screen
{
    /**
     * Display header name.
     *
     * @var string
     */
    public $name = 'Balance Correction';

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
        $brand = Brands::query()->where('id', $model->brand_id)->select('id', 'brand')->first();
        return [
            'overview' => [
                'basic_info' => [
                    'card_content' => [
                        'ID' => $model->id ?? '-',
                        'Partner' => Link::make($partner->partner)->route('platform.partners.view', $partner->id)->class('link-primary') ?? '-',
                        'Action' => $model->type_id ? $model::ACTION[$model->type_id] : '-',
                        'Amount' => $model->amount ?? '-',
                        'Currency' => Currency::query()->where('id', $model->currency_id)->select('name')->first()->name ?? '-',
                        'Brand' =>  $brand->brand ?? '-',
                        'Kind' => $model->kind_id ? $model::TYPE[$model->kind_id] : '-',
                        'Operator' => User::query()->where('id', $model->staff_id)->select('email')->first()->email ?? '-',
                        'Comment' => '-',
                        'Created at' => $model->created_at ?? '-',
                        'Reporting period start' => $model->report_period_from ?? '-',
                        'Reporting period end' => $model->report_period_to ?? '-',
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
