<?php

namespace App\Orchid\Screens\Affiliate;

use App\Models\CommissionBrands;
use App\Models\CommissionGroups;
use App\Models\Currency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Fields\CheckBox;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;
use App\Orchid\Screens\Affiliate\CommissionEdit;
use App\Orchid\Screens\Affiliate\CommissionsCreate;
use Orchid\Support\Facades\Toast;

class CommissionView extends Screen
{
    /**
     * Display header name.
     *
     * @var string
     */
    public $name = 'CommissionView';
    public $state;
    public $id;
    /**
     * Display header description.
     *
     * @var string|null
     */
    public $description = 'CommissionView';
    public $commission;
    public $request;
    public $default;

    /**
     *
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Query data.
     *
     * @return array
     */
    public function query(\App\Models\Commissions $model): array
    {
        
        $this->exist = $model->exists;
        if ($this->exist) {
            $this->name = $model->title;
            $this->description = 'id: ' . $model->id;
            $this->id = $model->id;
            $this->state = $model->state;
            $this->default = $model->default;
        }

        $brands = CommissionBrands::query()
            ->where('commission_brands.commission_id', $model->id)
            ->leftJoin('brands', 'commission_brands.brand_id', '=', 'brands.id')
            ->groupBy('brands.id')
            ->get();

        $groups = CommissionGroups::query()
            ->where('commission_groups.commission_id', $model->id)
            ->select('commission_groups.id', 'commission_rules.id as rule_id',
                'commission_rules.key as rule_key', 'commission_rules.value1 as rule_value1',
                'commission_rules.value2 as rule_value2',
                'commission_rules.type_id as rule_type', 'commission_modifiers.key as modifier_key',
                'commission_modifiers.value1 as modifier_value1', 'commission_modifiers.value2 as modifier_value2',
                'commission_modifiers.value3 as modifier_value3', 'commission_rules.rule_idx', 'currency.name as currency')
            ->leftJoin('commission_rules', 'commission_groups.id', '=', 'commission_rules.group_id')
            ->leftJoin('commission_modifiers', 'commission_rules.id', '=', 'commission_modifiers.rules_id')
            ->leftJoin('currency', 'commission_groups.currency_id', '=', 'currency.id')
            ->get()->toArray();

        $this->commission = $groups;
        switch ($model->strategy) {
            case '1':
                $strategy = 'cpa';
                break;
            case '2':
                $strategy = 'hybrid';
                break;
            case '3':
                $strategy = 'revshare';
                break;
            default:
                $strategy = '-';
        }

        switch ($model->state) {
            case '1':
                $state = 'published';
                break;
            case '2':
                $state = 'draft';
                break;
            case '3':
                $state = 'archived';
                break;
            default:
                $state = '-';
        }

        switch ($model->schedule_plan) {
            case '1':
                $schedule_plan = 'monthly';
                break;
            case '2':
                $schedule_plan = 'weekly';
                break;
            default:
                $schedule_plan = '-';
        }

        return [
            'commissions' => [
                'Title' => $model->title ?? '-',
                'Strategy' => $strategy,
                'Currency' => Currency::query()->where('id', $model->currency_id)->select('name')->first()->name ?? '-',
                'Campaign counts' => $model->campaign_counts ?? '-',
                'State' => $state,
                'Forgive debts' => '-',
                'Admin fee %' => $model->admin_fee ?? '-',
                'Schedule plan' => $schedule_plan,
                'Allow subaffiliates' => '-',
            ],
            'brand' => $brands,
            'constructor' => $this->get_constructor($groups),

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
            Button::make('Published')
                ->class('btn btn-outline-success active')
                ->confirm(__('Are you sure want to change the state?'))
                ->disabled($this->state === 1)
                ->parameters([
                    'state' => 1,
                    'id' => $this->id,
                ])
                ->method('change_state'),

            Button::make('Archived')
                ->class('btn btn-outline-danger active')
                ->method('change_state')
                ->confirm(__('Are you sure want to change the state?'))
                ->disabled($this->state === 3)
                ->parameters([
                    'state' => 3,
                    'id' => $this->id
                ]),

            Button::make('Delete')
                ->icon('ban')
                ->parameters([
                    'id' => $this->id,
                ])
                ->confirm('This commission will be deleted')
                ->canSee(!$this->default)
                ->method('delete_commission'),


            Button::make('Copy')
                ->icon('docs')
                ->parameters([
                    'id' => $this->id,
                ])
                ->method('copy_commission'),

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
            Layout::view('orchid.commission.commission-view'),
            Layout::table('brand', [
                TD::make('brand', 'Name')->sort()->align(TD::ALIGN_CENTER),
                TD::make()->render(function (CommissionBrands $model) {
                    return Input::make("brand[$model->id][brand_id]")
                        ->type('hidden')
                        ->value($model->id);
                })->sort()->align(TD::ALIGN_CENTER),
                TD::make('enabled', 'Enabled')->render(function (CommissionBrands $model) {
                    return CheckBox::make("brand[$model->id][enabled]")
                        ->sendTrueOrFalse(true)
                        ->value($model->enabled);
                })->sort(),
                TD::make('default', 'Default')->render(function (CommissionBrands $model) {
                    return CheckBox::make("brand[$model->id][default]")
                        ->sendTrueOrFalse(true)
                        ->value($model->default);
                })->sort(),
                TD::make('url', 'Balance Domain')
                    ->sort()->align(TD::ALIGN_CENTER),
            ]),

            Layout::view('orchid.commission.commission-view_constructor')

        ];
    }

    public function change_state(Request $request)
    {
        \App\Models\Commissions::query()->where('id', $request->id)->update(['state' => $request->state]);
    }

    public function delete_commission(Request $request){
        $input = $request->all();
        \App\Models\Commissions::query()->where('id', $input['id'])->delete();
        Toast::success(__('Success'));
        return redirect()->route('platform.affiliate.commissions');
    }

    public function copy_commission(Request $request, CommissionsCreate $create, CommissionEdit $commissionEdit)
    {

        $commission = \App\Models\Commissions::query()
            ->where('id', $request->id)
            ->select('brand_id', DB::raw('CONCAT(title, \' \', "- Copy") as title'), 'default', 'strategy',
                'schedule_plan', 'campaign_counts', 'state', 'forgive_fiat', 'forgive_crypto', 'admin_fee')
            ->first()
            ->toArray();

        $commission_id = $create->insert_commissions($commission);
        if ($commission_id) {
            $brand = CommissionBrands::query()
                ->where('commission_id', $request->id)
                ->select('brand_id', 'enabled', 'default')
                ->get()
                ->toArray();
            if ($brand) {
                $commission_brand = $create->get_brands($brand, $commission_id);
                $create->insert_brands($commission_brand);
            }
            $group = CommissionGroups::query()
                ->where('commission_id', $request->id)
                ->select('id', 'currency_id')
                ->get()
                ->toArray();

            if ($group) {
                $commission_group = $create->get_groups(array_column($group, 'currency_id'), $commission_id);
                $group_id = [];

                foreach ($commission_group as $kye => $group) {
                    $group_id[] = $create->insert_groups($group);
                }
            }

            $groups = CommissionGroups::query()
                ->where('commission_groups.commission_id', $request->id)
                ->select('commission_groups.id', 'commission_rules.id as rule_id',
                    'commission_rules.key as rule_key', 'commission_rules.value1 as rule_value1',
                    'commission_rules.value2 as rule_value2',
                    'commission_rules.type_id as rule_type', 'commission_modifiers.key as modifier_key',
                    'commission_modifiers.value1 as modifier_value1', 'commission_modifiers.value2 as modifier_value2',
                    'commission_modifiers.value3 as modifier_value3', 'commission_rules.rule_idx')
                ->leftJoin('commission_rules', 'commission_groups.id', '=', 'commission_rules.group_id')
                ->leftJoin('commission_modifiers', 'commission_rules.id', '=', 'commission_modifiers.rules_id')
                ->leftJoin('currency', 'commission_groups.currency_id', '=', 'currency.id')
                ->get()->toArray();
            $rules = $this->copy_constructor($groups);
            if ($rules && !empty($group_id)) {
                $create->get_rules($rules, $group_id);
            }
        }

        return redirect()->route('platform.affiliate.commissions.view', $commission_id);
    }

    public function change_query_id($query, $id, $field): array
    {
        return array_map(function ($q) use ($id, $field) {
            $q[$field] = $id;
            return $q;
        }, $query);
    }

    public function get_html_rule($type, $kye, $value1, $value2): string
    {
        $conditions = [
            'ngr' => 'Ngr',
            'sb_ngr' => 'Sb ngr',
            'wager' => 'Wager',
            'deposits_sum' => 'Deposits sum',
            'deposits_count' => 'Deposits count',
            'first_deposit_count' => 'First deposit count',
            'effective_deposits_sum' => 'Effective deposits sum',
            'depositing_players_count' => 'Depositing players count',
        ];
        $qualifier = [
            'wager' => 'Wager',
            'deposits_count' => 'Deposits count',
            'deposits_sum' => 'Deposits sum',
            'effective_deposits_sum' => 'Effective deposits sum',
            'first_deposit' => 'First deposit',
            'sb_bets_sum' => 'Sb bets sum',
        ];
        $reward = [
            'fixed' => 'Fixed',
            'fixed_per_player' => 'Fixed per player',
            'ngr_percent' => 'Ngr percent',
            'ngr_percent_per_player' => 'Ngr percent per player',
            'deposits_sum_percent' => 'Deposits sum percent ',
            'effective_deposits_sum_percent' => 'Effective deposits sum percent',
            'effective_deposits_sum_percent_per_player' => 'Effective deposits sum percent per player',
            'wager_percent' => 'Wager percent',
            'wager_percent_per_player' => 'Wager percent per player',
            'sb_ngr_percent' => 'Sb ngr percent',
            'sb_ngr_percent_per_player' => 'Sb ngr percent per player',
            'sb_bets_sum_percent' => 'Sb bets sum percent',
            'sb_bets_sum_percent_per_player' => 'Sb bets sum percent per player',
        ];
        $reward_unique = [
            'sb_bets_sum_percent_per_player',
            'sb_ngr_percent_per_player',
            'wager_percent_per_player',
            'effective_deposits_sum_percent_per_player',
            'ngr_percent_per_player',
            'fixed_per_player'];


        switch ($type) {
            case '1':
                $value = array_search($kye, array_flip($conditions));
                return "<div class='d-flex justify-content-between align-items-center'><b>$value:</b><p class='ml-1 m-0 text-right w-50'>$value1</p></div>";
            case '2':
                $value = array_search($kye, array_flip($qualifier));
                return "<div class='d-flex justify-content-between align-items-center'><b>$value:</b><p class='ml-1 m-0 text-right w-50'>$value1</p></div>";
            case '3':
                $value = array_search($kye, array_flip($reward));
                if (!in_array($kye, $reward_unique)) {
                    return "<div class='d-flex justify-content-between align-items-center'><b>$value:</b><p class='ml-1 m-0 text-right w-50'>$value1</p></div>";
                }
                switch ($kye) {
                    case 'sb_bets_sum_percent_per_player':
                        return
                            "<div class='d-flex justify-content-between align-items-center'>
                            <b>$value:</b><p class='ml-1 m-0 text-right w-50'>$value1</p>
                        </div>
                        <div class='d-flex justify-content-between align-items-center'>
                            <b>Max sportsbook bets sum percent:</b><p class='ml-1 m-0 text-right w-50'>$value2</p>
                        </div>";
                    case  'sb_ngr_percent_per_player':
                        return
                            "<div class='d-flex justify-content-between align-items-center'>
                            <b>$value:</b><p class='ml-1 m-0 text-right w-50'>$value1</p>
                        </div>
                        <div class='d-flex justify-content-between align-items-center'>
                            <b>Max SB NGR percent:</b><p class='ml-1 m-0 text-right w-50'>$value2</p>
                        </div>";
                    case  'wager_percent_per_player':
                        return
                            "<div class='d-flex justify-content-between align-items-center'>
                            <b>$value:</b><p class='ml-1 m-0 text-right w-50'>$value1</p>
                        </div>
                        <div class='d-flex justify-content-between align-items-center'>
                            <b>Max casino Wager percent:</b><p class='ml-1 m-0 text-right w-50'>$value2</p>
                        </div>";
                    case  'effective_deposits_sum_percent_per_player':
                        return
                            "<div class='d-flex justify-content-between align-items-center'>
                            <b>$value:</b><p class='ml-1 m-0 text-right w-50'>$value1</p>
                        </div>
                        <div class='d-flex justify-content-between align-items-center'>
                            <b>Max effective deposits sum percent:</b><p class='ml-1 m-0 text-right w-50'>$value2</p>
                        </div>";
                    case  'ngr_percent_per_player':
                        return
                            "<div class='d-flex justify-content-between align-items-center'>
                            <b>$value:</b><p class='ml-1 m-0 text-right w-50'>$value1</p>
                        </div>
                        <div class='d-flex justify-content-between align-items-center'>
                            <b>Max casino NGR percent:</b><p class='ml-1 m-0 text-right w-50'>$value2</p>
                        </div>";
                    case  'fixed_per_player':
                        return
                            "<div class='d-flex justify-content-between align-items-center'>
                            <b>$value:</b><p class='ml-1 m-0 text-right w-50'>$value1</p>
                        </div>
                        <div class='d-flex justify-content-between align-items-center'>
                            <b>Max fixed reward:</b><p class='ml-1 m-0 text-right w-50'>$value2</p>
                        </div>";
                }

        }

        return '';
    }

    public function get_html_modifier($type, $kye, $value1, $value2, $value3): string
    {
        $modifier = [
            'activity_from' => 'Activity from',
            'omit_duplicates' => 'Omit duplicates',
            'omit_self_exclusions' => 'Omit self exclusions',
            'omit_disabled_players' => 'Omit disabled players',
            'period_since_registration' => 'Period since registration',
            'with_deposits' => 'With deposits',
            'qualified_players_only' => 'Qualified players only',
            'not_qualified_players_only' => 'Not qualified players only',
            'not_qualified_in_any_brand_players_only' => 'Not qualified in any brand players only',
            'player_countries' => 'Player countries',
        ];

        $unique = [
            'period_since_registration',
            'with_deposits', 'activity_from'
        ];

        $option = [
            "day" => 'Day', "week" => "Week", "month" => "Month", "from_last_bill" => 'Form last bill',
            "from_last_issued_bill" => "Form last issued bill", "days_count" => "Days count"
        ];

        if ($type) {
            $value = array_search($kye, array_flip($modifier));
            if (!in_array($kye, $unique)) {
                return "<div class='d-flex justify-content-between align-items-center'><b>$value:</b><p class='ml-1 m-0 text-right w-50'>$value1</p></div>";
            }
            if ($kye === 'activity_from' || $kye === 'period_since_registration') {
                $form = array_search($value1, array_flip($option));
                if ($value1 != 'days_count') {
                    return "<div class='d-flex justify-content-between align-items-center'><b>$value:</b><p class='ml-1 m-0 text-right w-50'>$form</p></div>";
                }
                return
                    "<div class='d-flex justify-content-between align-items-center'><b>$value:</b><p class='ml-1 m-0 text-right w-50'>$form</p></div>
                         <div class='d-flex justify-content-between align-items-center'><b>Count:</b><p class='ml-1 m-0 text-right w-50'>$value2</p></div>";
            }
            if ($kye === 'with_deposits') {
                $form = array_search($value2, array_flip($option));
                if ($value2 != 'days_count') {
                    return "<div class='d-flex justify-content-between align-items-center'><b>$value:</b><p class='ml-1 m-0 text-right w-50'>$form</p></div>
                                <div class='d-flex justify-content-between align-items-center'><b>Min deposits count:</b><p class='ml-1 m-0 text-right w-50'>$value1</p></div>";
                }
                return
                    "<div class='d-flex justify-content-between align-items-center'><b>$value:</b><p class='ml-1 m-0 text-right w-50 '>$form</p></div>
                         <div class='d-flex justify-content-between align-items-center'><b>Min deposits count:</b><p class='ml-1 m-0 text-right w-50'>$value1</p></div>
                         <div class='d-flex justify-content-between align-items-center'><b>Count:</b><p class='ml-1 m-0 text-right w-50'>$value3</p></div>";
            }
        }

        return '';
    }

    public function get_constructor($query): array
    {
        $groups = array_map(function ($group) {

            return [$group['id'] => [
                'currency' => $group['currency'],
                'rules' => [
                    $group['rule_idx'] => [
                        $group['rule_id'] => [
                            $group['rule_type'] => [
                                'rule_key' => $group['rule_key'],
                                'rule_value1' => $group['rule_value1'],
                                'rule_value2' => $group['rule_value2'],
                                'html' => $this->get_html_rule($group['rule_type'], $group['rule_key'], $group['rule_value1'], $group['rule_value2']),
                                'modifier' => [
                                    ['modifier_key' => str_replace(['_'], ' ', $group['modifier_key']),
                                        'modifier_value1' => $group['modifier_value1'],
                                        'modifier_value2' => $group['modifier_value2'],
                                        'modifier_value3' => $group['modifier_value3'],
                                        'html' => $this->get_html_modifier($group['rule_type'], $group['modifier_key'], $group['modifier_value1'], $group['modifier_value2'], $group['modifier_value3'])]
                                ]
                            ]
                        ]
                    ]
                ]]];
        }, $query);

        $constructor = [];
        $old_group_id = '';
        $old_rule_id = 0;
        $group_count = 0;
        foreach ($groups as $idx => $group) {

            foreach ($group as $group_id => $value) {

                if ($old_group_id !== $group_id) {
                    $group_count++;
                    $constructor[$group_count] = $value;
                    $old_group_id = $group_id;
                    $old_rule_id = 0;
                }

                foreach ($value['rules'] as $rule => $type) {
                    if (!$rule) {
                        continue;
                    }

                    foreach ($type as $rule_id => $v) {
                        if ($old_rule_id !== $rule_id) {
                            $constructor[$group_count]['rules'][$rule][$rule_id] = $v;
                            $old_rule_id = $rule_id;
                            continue;
                        }
                        foreach ($v as $k => $modifier) {
                            array_push($constructor[$group_count]['rules'][$rule][$rule_id][$k]['modifier'], $modifier['modifier'][0]);
                        }
                        $old_rule_id = $rule_id;
                    }
                }
                $old_group_id = $group_id;
            }
        }

        return $constructor;

    }

    public function copy_constructor($query): array
    {
        $groups = array_map(function ($group) {

            return [$group['id'] => [
                $group['rule_idx'] => [
                    $group['rule_id'] => [
                        $group['rule_type'] => [
                            0 => $group['rule_key'],
                            1 => $group['rule_value1'],
                            2 => $group['rule_value2'],
                            'modifier' => [
                                [
                                    0 => $group['modifier_key'],
                                    1 => $group['modifier_value1'],
                                    2 => $group['modifier_value2'],
                                    3 => $group['modifier_value3'],
                                ]
                            ]
                        ]
                    ]
                ]
            ]];
        }, $query);

        $constructor = [];
        $old_group_id = '';
        $old_rule_id = 0;
        $group_count = 0;
        $idx_group = 0;
        $old_rule_idx = '';
        foreach ($groups as $idx => $group) {
            foreach ($group as $group_id => $value) {
                if ($old_group_id !== $group_id) {
                    $idx_group = $group_count++;;
                    $constructor[$idx_group] = [];
                    $old_group_id = $group_id;
                    $old_rule_id = 0;
                    $condition_id = 0;
                    $qualifiers_id = 0;
                    $rewards_id = 0;
                }
                foreach ($value as $rule => $type) {
                    if (!$rule) {
                        continue;
                    }
                    if ($old_rule_idx !== $rule) {
                        $condition_id = 0;
                        $qualifiers_id = 0;
                        $rewards_id = 0;
                    }
                    $old_rule_idx = $rule;
                    foreach ($type as $rule_id => $v) {
                        if ($old_rule_id === $rule_id) {
                            $condition_id = 0;
                            $qualifiers_id = 0;
                            $rewards_id = 0;
                            $idx_type = 0;
                        }
                        switch (array_key_first($v)) {
                            case 1:
                                $idx_type = $condition_id++;
                                $type = '1';
                                break;
                            case 2:
                                $idx_type = $qualifiers_id++;
                                $type = '2';
                                break;
                            case 3:
                                $idx_type = $rewards_id++;
                                $type = '3';
                                break;
                        }

                        foreach ($v as $k => $modifier) {
                            if ($old_rule_id !== $rule_id) {

                                $constructor[$idx_group][$rule - 1][$type][$idx_type] = $modifier;
                                $old_rule_id = $rule_id;
                                continue;
                            }
                            $constructor[$idx_group][$rule - 1][$type][$idx_type]['modifier'][] = $modifier['modifier'][0];
                        }
                        $old_rule_id = $rule_id;
                    }
                }

                $old_group_id = $group_id;
            }
        }

        return $constructor;
    }
}
