<?php

namespace App\Orchid\Screens\Affiliate;

use App\Models\CommissionGroups;
use App\Models\CommissionModifiers;
use App\Models\CommissionRules;
use App\Models\Currency;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Illuminate\Http\Request;
use App\Orchid\Screens\Affiliate\CommissionsCreate;

class CommissionEdit extends Screen
{
    /**
     * Display header name.
     *
     * @var string
     */
    public $name = 'CommissionEdit';
    public $id;
    /**
     * Display header description.
     *
     * @var string|null
     */
    public $description = 'CommissionEdit';

    public $request;

    /**
     *
     * @param \Illuminate\Http\Request $request
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
        }

        $groups = CommissionGroups::query()
            ->where('commission_groups.commission_id', $model->id)
            ->select('commission_groups.id', 'commission_rules.id as rule_id',
                'commission_rules.key as rule_key', 'commission_rules.value1 as rule_value1',
                'commission_rules.value2 as rule_value2',
                'commission_rules.type_id as rule_type', 'commission_modifiers.key as modifier_key',
                'commission_modifiers.value1 as modifier_value1', 'commission_modifiers.value2 as modifier_value2',
                'commission_modifiers.value3 as modifier_value3', 'commission_rules.rule_idx')
            ->leftJoin('commission_rules', 'commission_groups.id', '=', 'commission_rules.group_id')
            ->leftJoin('commission_modifiers', 'commission_rules.id', '=', 'commission_modifiers.rules_id')
            ->get()->toArray();

        $groups = array_map(function ($group) {
            for ($i = 1; $i <= 3; $i++) {
                if ($group["modifier_value$i"]) {
                    if ($group["modifier_value$i"] === 'on') {
                        $group["modifier_value$i"] = true;
                    }
                    if ($group["modifier_value$i"] === 'of') {
                        $group["modifier_value$i"] = false;
                    }
                }
            }
            return $group;
        }, $groups);

        return [
            'common_data' => [
                'currency' => Currency::query()->select('name as code', 'id')->get(),
                'groups' => $this->get_constructor($groups),
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
        return [
            Button::make('Save')
                ->icon('check')
                ->parameters(['id' => $this->id])
                ->method('save_constructor'),
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
            Layout::view('new_group')
        ];
    }

    public function save_constructor(Request $request, CommissionsCreate $create)
    {
        $group_id = CommissionGroups::query()
            ->where('commission_id', $request->id)
            ->select('id')
            ->get()
            ->toArray();

        CommissionGroups::query()->whereIn('id', array_column($group_id, 'id'))->delete();

        $rule_id = CommissionRules::query()
            ->whereIn('group_id', $group_id)
            ->select('id')
            ->get()
            ->toArray();

        CommissionRules::query()->whereIn('id', array_column($rule_id, 'id'))->delete();

        $modifier_id = CommissionModifiers::query()
            ->whereIn('rules_id', $rule_id)
            ->select('id')
            ->get()
            ->toArray();

        CommissionModifiers::query()->whereIn('id', array_column($modifier_id, 'id'))->delete();

        if ($request->group_currency) {
            $commission_group = $create->get_groups($request->group_currency, $request->id);
            $group_id = [];

            foreach ($commission_group as $kye => $group) {
                $group_id[] = $create->insert_groups($group);
            }

            if ($request->group && !empty($group_id)) {
                $create->get_rules($request->group, $group_id);
            }
        }

    }


    public function get_constructor($query): array
    {

        $groups = array_map(function ($group) {

            return [$group['id'] => [
                $group['rule_idx'] => [
                    $group['rule_id'] => [
                        $group['rule_type'] => [
                            'key' => $group['rule_key'],
                            'value1' => $group['rule_value1'],
                            'value2' => $group['rule_value2'],
                            'modifier' => [
                                [
                                    'key' => $group['modifier_key'],
                                    'value1' => $group['modifier_value1'],
                                    'value2' => $group['modifier_value2'],
                                    'value3' => $group['modifier_value3'],
                                ]
                            ]
                        ]
                    ]
                ]
            ]
            ];
        }, $query);

        $constructor = [];
        $old_group_id = '';
        $old_rule_id = '';
        $old_rule_idx = '';
        $group_count = 0;
        $idx_type = 0;
        $idx_group = 0;
        $old_idx_type = '';
        $modifier_id = 1;
        foreach ($groups as $group) {
            foreach ($group as $group_id => $value) {
                if ($old_group_id !== $group_id) {
                    $idx_group = $group_count++;;
                    $constructor[$idx_group] = ['id' => $group_count, 'value' => 1, 'rules' => []];
                    $old_rule_idx = 0;
                }
                foreach ($value as $rule => $type) {
                    if (!$rule) {
                        continue;
                    }
                    if ($old_rule_idx !== $rule) {
                        $constructor[$idx_group]['rules'][$rule - 1] = ['condition' => [], 'qualifiers' => [], 'rewards' => []];
                        $condition_id = 0;
                        $qualifiers_id = 0;
                        $rewards_id = 0;
                        $modifier_condition = 0;
                        $modifier_qualifiers = 0;
                        $modifier_rewards = 0;
                    }

                    $old_rule_idx = $rule;
                    foreach ($type as $rule_id => $v) {
                        if ($old_rule_id === $rule_id) {
                            $condition_id = 0;
                            $qualifiers_id = 0;
                            $rewards_id = 0;
                            $idx_type = 0;
                            $old_idx_type = '';
                        } else {
                            $modifier_condition = 0;
                            $modifier_qualifiers = 0;
                            $modifier_rewards = 0;
                        }
                        switch (array_key_first($v)) {
                            case 1:
                                $modifier_id = ++$modifier_condition;
                                $idx_type = $condition_id++;
                                $id_type = $condition_id;
                                $type_rule = 'condition';
                                break;
                            case 2:
                                $modifier_id = ++$modifier_qualifiers;
                                $idx_type = $qualifiers_id++;
                                $id_type = $qualifiers_id;
                                $type_rule = 'qualifiers';
                                break;
                            case 3:
                                $modifier_id = ++$modifier_rewards;
                                $idx_type = $rewards_id++;
                                $id_type = $rewards_id;
                                $type_rule = 'rewards';
                                break;
                        }

                        if ($old_rule_id !== $rule_id) {
                            $v = array_merge($v[array_key_first($v)], ['id' => $id_type]);
                            if (!$v['modifier'][0]['key']) {
                                $v = array_filter($v, function ($v, $k) {
                                    return $k !== 'modifier';
                                }, ARRAY_FILTER_USE_BOTH);
                                $v = array_merge($v, ['modifier' => []]);
                            } else {
                                $modifier = array_map(function ($modifier) use ($modifier_id) {
                                    return array_merge($modifier, ['id' => $modifier_id]);
                                }, $v['modifier']);
                                $v['modifier'][0] = $modifier[0];
                            }
                            $constructor[$idx_group]['rules'][$rule - 1][$type_rule][$idx_type] = $v;
                        } else {
                            foreach ($v as $k => $modifier) {
                                if ($modifier['modifier'][0]['key'] === null) {
                                    array_filter($modifier, function ($v, $k) {
                                        return $k !== 'modifier';
                                    }, ARRAY_FILTER_USE_BOTH);
                                }
                                $modifier = array_merge($modifier['modifier'][0], ['id' => $modifier_id]);
                                $constructor[$idx_group]['rules'][$rule - 1][$type_rule][$idx_type]['modifier'][] = $modifier;
                            }
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
