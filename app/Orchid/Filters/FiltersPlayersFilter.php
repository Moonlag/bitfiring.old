<?php

namespace App\Orchid\Filters;


use App\Models\Bonuses;
use App\Models\FreespinIssue;
use App\Models\Sessions;
use Illuminate\Database\Eloquent\Builder;
use Orchid\Filters\Filter;
use Orchid\Screen\Field;

class FiltersPlayersFilter extends Filter
{
    /**
     * @var array
     */
    public $parameters = [
        'data'
    ];

    /**
     * @return string
     */
    public function name(): string
    {
        return '';
    }

    /**
     * @param Builder $builder
     *
     * @return Builder
     */
    public function run(Builder $builder): Builder
    {

        if ($this->request->get('data')) {
            $data = $this->request->get('data');
            foreach ($data as $key => $value) {
                if (isset($value['model'][3])) {
                    if($value['model'][3]['value1'] === 'include'){
                        if(isset($value['model'][23]) && !empty($value['model'][23]['value1'])){
                            $builder = $builder->whereIn('players.id', $value['model'][23]['value1']);
                        }

                        if (isset($value['model'][28]) && !empty($value['model'][28]['value1'])) {
                            $builder = $builder->whereIn('players.id', $value['model'][28]['value1']);

                        }
                    }
                    if($value['model'][3]['value1'] === 'exclude'){
                        if(isset($value['model'][23]) && !empty($value['model'][23]['value1'])){
                            $builder = $builder->whereNotIn('players.id', $value['model'][23]['value1']);
                        }

                        if (isset($value['model'][28]) && !empty($value['model'][28]['value1'])) {
                            $builder = $builder->whereNotIn('players.id',$value['model'][28]['value1']);
                        }
                    }
                }
                if(isset($value['model'][4]) && !empty($value['model'][4]['value1'])){
                    $builder = $builder->where('players.address', 'LIKE', "%{$value['model'][4]['value1']}%");
                }
                if(isset($value['model'][5])){
                    if($value['model'][5]['value2'] === '1'){
                        foreach ($value['model'][5]['value1'] as $currency){
                            $bonuses =  Bonuses::query()
                                ->leftJoin('bonus_issue', 'components.id', '=', 'bonus_issue.bonus_id')
                                ->where('components.currency_id', $currency['id'])
                                ->where('components.min', $currency['min'])
                                ->select('bonus_issue.user_id as id')->get()->toArray();
                            $builder = $builder->whereIn('players.id', array_column($bonuses, 'id'));
                        }
                    }
                    if($value['model'][5]['value2'] === '2'){
                        foreach ($value['model'][5]['value1'] as $currency){
                            $bonuses =  Bonuses::query()
                                ->leftJoin('bonus_issue', 'components.id', '=', 'bonus_issue.bonus_id')
                                ->where('components.currency_id', $currency['id'])
                                ->where('components.max', $currency['max'])
                                ->select('bonus_issue.user_id as id')->get()->toArray();
                            $builder = $builder->whereIn('players.id', array_column($bonuses, 'id'));
                        }
                    }
                }
                if(isset($value['model'][16])){
                    $bonuses = Bonuses::query()->leftJoin('bonus_issue', 'components.id', '=', 'bonus_issue.bonus_id')
                        ->where('components.status', (int)$value['model'][16]['value1'])
                        ->select('bonus_issue.user_id as id')->get()->toArray();
                    $builder = $builder->whereIn('players.id', array_column($bonuses, 'id'));
                }
                if($value['id'] === 32){
                    if(isset($value['model'][34])){
                        $bonuses = Bonuses::query()->leftJoin('bonus_issue', 'components.id', '=', 'bonus_issue.bonus_id')
                            ->where('components.title', (int)$value['model'][34]['value1'])
                            ->select('bonus_issue.user_id as id')->get()->toArray();
                        $builder = $builder->whereIn('players.id', array_column($bonuses, 'id'));
                    }
                }
                if($value['id'] === 33){
                    if(isset($value['model'][34])){
                        $bonuses = FreespinIssue::query()
                            ->where('freespin_issue.title', (int)$value['model'][34]['value1'])
                            ->select('bonus_issue.user_id as id')->get()->toArray();
                        $builder = $builder->whereIn('players.id', array_column($bonuses, 'id'));
                    }
                }

                if(isset($value['model'][20])){
                    $builder = $builder->where('players.status', '=', (int) $value['model'][20]['value1']);
                }
                if(isset($value['model'][22]) && !empty($value['model'][22]['value1'])){
                    $builder = $builder->whereIn('players.currency_id', array_column($value['model'][22]['value1'], 'id'));
                }
                if(isset($value['model'][24])){
                    $model = $value['model'][24];
                    if ($model['value1'] === 'After' && !empty($model['value2'])){
                        $builder = $builder->where('players.dob', '>=', $model['value2']);
                    }
                    if ($model['value1'] === 'Before' && !empty($model['value2'])){
                        $builder = $builder->where('players.dob', '<=', $model['value2']);
                    }
                    if ($model['value1'] === 'Between' && !empty($model['value2']) && !empty($model['value3'])){
                        $builder = $builder->whereBetween('players.dob', [$model['value2'], $model['value3']]);
                    }
                }
                if(isset($value['model'][31]) && !empty($value['model'][31]['value1'])){
                    $builder = $builder->where('players.gender', '=', (int) $value['model'][31]['value1']);
                }
                if (isset($value['model'][39])) {
                    $builder = $builder->where('players.promo_sms', '=', (int) $value['model'][39]['value1']);
                }
                if (isset($value['model'][38])) {
                    $builder = $builder->where('players.promo_email', '=', (int) $value['model'][38]['value1']);
                }
            }
        }

        return $builder;
    }

    /**
     * @return Field[]
     */
    public
    function display(): array
    {
        //
    }
}
