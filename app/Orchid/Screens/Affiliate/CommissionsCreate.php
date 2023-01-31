<?php

namespace App\Orchid\Screens\Affiliate;

use App\Models\CommissionBrands;
use App\Models\CommissionGroups;
use App\Models\CommissionModifiers;
use App\Models\CommissionRules;
use App\Models\Commissions;
use App\Models\Currency;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Fields\CheckBox;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class CommissionsCreate extends Screen
{
    /**
     * Display header name.
     *
     * @var string
     */
    public $name = 'CommissionsCreate';
    public $test;
    /**
     * Display header description.
     *
     * @var string|null
     */
    public $description = 'CommissionsCreate';

    /**
     * Query data.
     *
     * @return array
     */
    public function query(): array
    {
        $brand = \App\Models\Brands::query()->select('id', 'brand', 'url')->paginate(7);
        return [
            'brand' => $brand,
            'common_data' => [
                'currency' => Currency::query()->select('name as code', 'id')->get()
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
            Button::make('Save')
                ->icon('check')
                ->method('apply_filter'),
            Link::make('Cancel')
                ->icon('left')
                ->route('platform.affiliate.commissions'),
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
            Layout::wrapper('admin.wrapperTable', [
                'header' => Layout::view('admin.headerTable', ['title' => 'Details',]),
                'table' =>
                    Layout::rows([
                        Input::make('commission.title')
                            ->title('Title'),

                        Select::make('commission.strategy')
                            ->options([
                                1 => 'Cpa',
                                2 => 'Hybrid',
                                3 => 'Revshare'
                            ])
                            ->title('Strategy'),

                        Select::make('fiat_currency')
                            ->title('Fiat currency')
                            ->fromQuery(Currency::query()->where('parent_id', '=' ,0), 'name'),

                        Input::make('commission.forgive_fiat')
                            ->title('Forgive debits in fiat currency up to'),

                        Input::make('commission.forgive_crypto')
                            ->title('Forgive debits in cryptocurrency up to'),

                        Input::make('commission.admin_fee')
                            ->title('Admin fee %'),

                        Select::make('commission.schedule_plan')
                            ->options(
                                [
                                    1 => 'Monthly',
                                    2 => 'Weekly',
                                ]
                            )
                            ->title('Schedule_plan'),


                    ]),
            ]),
            Layout::wrapper('admin.wrapperTable', [
                'header' => Layout::view('admin.headerTable', ['title' => 'Brands',]),
                'table' => Layout::table('brand', [
                    TD::make('id', 'ID')->sort()->align(TD::ALIGN_CENTER),
                    TD::make()->render(function (\App\Models\Brands $model) {
                        return Input::make("brand[$model->id][brand_id]")
                            ->type('hidden')
                            ->value($model->id);
                    })->sort()->align(TD::ALIGN_CENTER),
                    TD::make('enabled', 'Enabled')->render(function (\App\Models\Brands $model) {
                        return CheckBox::make("brand[$model->id][enabled]")
                            ->sendTrueOrFalse(true);
                    })->sort(),
                    TD::make('default', 'Default')->render(function (\App\Models\Brands $model) {
                        return CheckBox::make("brand[$model->id][default]")
                            ->sendTrueOrFalse(true);
                    })->sort(),
                    TD::make('url', 'Balance Domain')
                        ->sort()->align(TD::ALIGN_CENTER),
                ]),
            ]),
            Layout::view('new_group')
        ];

    }

    public function clear_filter()
    {
        Toast::info(__('Filter cleared'));
        return redirect()->route('platform.affiliate.commissions.create');
    }

    public function apply_filter(Request $request)
    {

        if ($request->commission) {
            $commission_id = $this->insert_commissions($request->commission);
            if ($commission_id){
                if ($request->brand) {
                    $commission_brand = $this->get_brands($request->brand, $commission_id);
                    $this->insert_brands($commission_brand);
                }

                if ($request->group_currency) {
                    $commission_group = $this->get_groups($request->group_currency, $commission_id);
                    $group_id = [];

                    foreach ($commission_group as $kye => $group){
                        $group_id[] = $this->insert_groups($group);
                    }
                }
                if ($request->group && !empty($group_id)) {
                    $this->get_rules($request->group, $group_id);
                }
            }
        }
        Alert::success('Filter apply');
        return redirect()->route('platform.affiliate.commissions.create', array_filter($request->all(), function ($k, $v) {
            return $k != '0' && $v != '_token';
        }, ARRAY_FILTER_USE_BOTH));
    }

    public function get_rules($request, $groups_id)
    {
        foreach ($request as $idx_group => $group) {
            $group_id = $groups_id[$idx_group];
            foreach ($group as $k_rule => $rule) {
                foreach ($rule as $k_type => $type) {

                        $commission_rule = array_map(function ($v) use ($k_type, $group_id, $k_rule){

                           return ['key' => $v['0'] ?? null, 'value1' => $v['1'] ?? null, 'value2' => $v['2'] ?? null,
                               'type_id' => $k_type, 'group_id' => $group_id, 'rule_idx' => $k_rule+1,
                               'modifier' => $v['modifier'] ?? null];
                        }, $type);
                        if (empty($commission_rule)) {
                            continue;
                        }

                        foreach ($commission_rule as $rule) {
                            if ($rule['modifier']) {
                                $commission_rule_id = $this->insert_rules(array_filter($rule, function ($k, $v){
                                    return $v != 'modifier';
                                }, ARRAY_FILTER_USE_BOTH));
                                if($rule['modifier'] !== null){
                                    $commission_modifier = array_map(function ($v) use ($commission_rule_id){
                                        return ['key' => $v[0] ?? null, 'value1' => $v[1] ?? null,
                                            'value2' => $v[2] ?? null, 'value3' => $v[3] ?? null,
                                            'rules_id' => $commission_rule_id];
                                    }, $rule['modifier']);
                                    $this->insert_modifiers($commission_modifier);
                                }
                            } else {
                                $this->insert_rules(array_filter($rule, function ($k, $v){
                                    return $v != 'modifier';
                                }, ARRAY_FILTER_USE_BOTH));
                        }
                    }
                }
            }
        }

    }

    public function get_brands($request, $commission_id): array
    {
        return array_map(function ($v) use ($commission_id) {
            return array_merge($v, ['commission_id' => $commission_id]);
        }, array_values(array_filter($request, function ($v, $k) {
            return $v['enabled'] != 0;
        }, ARRAY_FILTER_USE_BOTH)));
    }

    public function get_groups($request, $commission_id): array
    {
        return array_map(function ($v) use ($commission_id) {
            return ['currency_id' => $v, 'commission_id' => $commission_id];
        }, $request);
    }


    public function insert_commissions($request): int
    {
        return Commissions::query()->insertGetId($request);
    }

    public function insert_brands($commission_brand)
    {
        CommissionBrands::query()->insert($commission_brand);
    }

    public function insert_groups($commission_group): int
    {
        return CommissionGroups::query()->insertGetId($commission_group);
    }

    public function insert_modifiers($commission_modifier)
    {
        CommissionModifiers::query()->insert($commission_modifier);
    }

    public function insert_rules($commission_rule): int
    {
        return CommissionRules::query()->insertGetId($commission_rule);
    }
}
