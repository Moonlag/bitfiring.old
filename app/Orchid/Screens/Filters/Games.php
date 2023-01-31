<?php

namespace App\Orchid\Screens\Filters;

use App\Models\Currency;
use App\Models\GameBonusContributions;
use App\Models\GameCategoryBinds;
use App\Models\GameCurrency;
use App\Models\GamesCats;
use App\Models\GamesProvider;
use App\Models\GamesSorting;
use App\Models\ProducerSorting;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use phpbb\extension\exception;

class Games extends Screen
{
//    /**
//     * Display header name.
//     *
//     * @var string
//     */
//    public $name = 'Games';

//    /**
//     * Display header description.
//     *
//     * @var string|null
//     */
//    public $description = 'Games';

    public $permission = [
        'platform.games'
    ];

    public $request;

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
    public function query(): array
    {

//        DB::enableQueryLog();
        $games = \App\Models\Games::query()
            ->leftJoin('games_cats', 'games.category_id', '=', 'games_cats.id')
            ->Join('game_provider', 'games.provider_id', '=', 'game_provider.id')
            ->leftJoin('game_bonus_contributions', 'games.id', '=', 'game_bonus_contributions.game_id')
            ->leftJoin('games_sorting', 'games.id', '=', 'games_sorting.game_id')
            ->select('games.id', 'games.name', 'games.provider', 'games.producer',
                'games_cats.title as category', 'games.identer as identifier',
                'games.devices', 'games.fs', 'games.jp', 'games.id', 'game_provider.title', 'game_provider.id as provider_id',
                'games.multiplier', 'games.published as active', 'game_provider.fee',
                'game_bonus_contributions.value as gbc_value', 'game_bonus_contributions.default_value as gbc_default',
                'games_sorting.weight', 'game_provider.code as producer')
            ->orderBy('game_provider.id', 'ASC')
            ->get();


        $games_currency = GameCurrency::query()->select('game_id', 'currency_id')->get();
        $providers = GamesProvider::query()->select('game_provider.*', 'producers_sorting.weight')
            ->leftJoin('producers_sorting', 'game_provider.id', '=', 'producers_sorting.producer_id')
            ->groupBy('game_provider.id')
            ->get();
        $games_cats = GamesCats::query()->get()->toArray();
        $game_category_binds = GameCategoryBinds::query()->select('game_category_binds.*')->join('games', 'game_category_binds.game_id', '=', 'games.id')->groupBy('game_category_binds.id')->get();
        $currency = Currency::query()->select('id', 'code')->whereIn('id', [7,9,12,14])->get();

//        $currency = array_map(function ($currency) {
//            return array_merge($currency, ['value' => false]);
//        }, $currency);

//        $games = array_map(function ($game, $k) use ($currency, $games_currency) {
//            return [
//                'title' => $game['title'],
//                'currency_model' => $currency,
//                'provider_id' => $game['provider_id'],
//                'weight' => $game['producers_weight'],
//                'select_all' => false,
//                'bonus_select_all' => false,
//                'sorting_select_all' => false,
//                'cat_select_all' => false,
//                'tbody' => [
//                    [
//                        'name' => $game['name'] ?? '-',
//                        'indentifier' => $game['identifier'],
//                        'producer' => $game['provider'],
//                        'category' => $game['category'],
//                        'fee_group' => null,
//                        'devices' => $this->devices($game['devices']), 'fs' => $game['fs'],
//                        'jp' => $game['jp'],
//                        'fee' => $game['fee'],
//                        'multiplier' => $game['multiplier'],
//                        'active_game' => $game['active'],
//                        'id' => $game['id'],
//                        'gbc_value' => $game['gbc_value'],
//                        'gbc_default' => $game['gbc_default'],
//                        'game_model' => false,
//                        'bonus_model' => false,
//                        'sorting_model' => false,
//                        'cat_model' => false,
//                        'currency' => $this->currency_edit($game['id'], $currency, $games_currency),
//                        'weight' => $game['weight'] ?? null
//                    ],
//                ]
//            ];
//        }, $games, array_keys($games));

        $idx = -1;
        $old_group = '';

//        foreach ($games as $key => $value) {
//            if ($old_group !== $value['title']) {
//                $data[++$idx] = $value;
//                $data[$idx]['id'] = $idx;
//                $old_group = $value['title'];
//                continue;
//            }
//            array_push($data[$idx]['tbody'], $value['tbody'][0]);
//        }

//        dd(DB::getQueryLog());
        return ['common_data' => [
            'games' => $games,
            'providers' => $providers,
            'currency' => $currency,
            'games_currency' => $games_currency,
            'game_cat' => $games_cats,
            'cat_binds' => $game_category_binds
        ]];
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
            Layout::view('orchid.filters.filters-game'),
        ];
    }

    public function get_sorting()
    {

    }

    public function devices($value): string
    {
        switch ($value) {
            case 1:
                return 'DM';
            case 2:
                return 'D';
            case 3:
                return 'M';
            default:
                return '-';
        }
    }

    public function currencies_save(Request $request)
    {
        $result = $request->all();

        if (!empty($result)) {
            $del_id = array_values(array_unique(array_column($result, 'game_id')));
            $result = array_values(array_filter($result, function ($v, $k) {
                return $v['currency_id'] !== null;
            }, 1));
            $validator = Validator::make($result, [
                '*.game_id' => 'required|integer',
                '*.currency_id' => 'required|integer',
            ]);
            if ($validator->fails()) {
                return new JsonResponse(['error' => 'Invalid request'], 400);
            }

            GameCurrency::query()->whereIn('game_id', $del_id)->delete();
            GameCurrency::query()->insert($result);
        }
        return new JsonResponse([['msg' => 'Success']], 200);
    }

    public function bonus_contribution(Request $request)
    {
        $result = $request->all();
        $validator = Validator::make($result, [
            '*.game_id' => 'required|integer',
            '*.value' => 'required|integer|min:0|max:100',
        ]);
        if ($validator->fails()) {
            return new JsonResponse(['error' => 'Invalid request'], 400);
        }
        foreach ($result as $res) {
            GameBonusContributions::updateOrCreate(['game_id' => $res['game_id']], ['value' => $res['value']]);
        }
        return new JsonResponse(['msg' => 'Success'], 200);
    }

    public function save_sorting(Request $request)
    {
        $result = $request->all();
        $del_id = array_values(array_unique(array_column($result, 'game_id')));

        $validator = Validator::make($result, [
            '*.game_id' => 'required|integer',
            '*.weight' => 'required|integer|min:0',
        ]);
        if ($validator->fails()) {
            return new JsonResponse(['error' => 'Invalid request'], 400);
        }
        GamesSorting::query()->delete();
        GamesSorting::query()->insert($result);

        return new JsonResponse(['msg' => 'Success'], 200);
    }

    public function save_sorting_producer(Request $request)
    {
        $result = $request->all();
        $del_id = array_values(array_unique(array_column($result, 'producer_id')));

        $validator = Validator::make($result, [
            '*.producer_id' => 'required|integer',
            '*.weight' => 'required|integer|min:0',
        ]);
        if ($validator->fails()) {
            return new JsonResponse(['error' => 'Invalid request'], 400);
        }
        ProducerSorting::query()->whereIn('producer_id', $del_id)->delete();
        ProducerSorting::query()->insert($result);
        return new JsonResponse(['msg' => 'Success'], 200);
    }

    public function currency_edit($game_id, $currency, $games_currency): array
    {
        return array_map(function ($currency) use ($game_id, $games_currency) {
            $id = $currency['id'];
            return [
                'id' => $currency['id'],
                'code' => $currency['code'],
                'value' => !empty(array_filter($games_currency, function ($v, $k) use ($game_id, $id) {
                    return $v['game_id'] === $game_id && $v['currency_id'] === $id;
                }, 1))
            ];
        }, $currency);
    }

    public function cats_save(Request $request)
    {
        $result = $request->all();

        foreach ($result as $value) {
            GamesCats::query()->where('id', $value['category_id'])->update(['enabled' => $value['enabled'], 'weight' => $value['weight']]);
        }

        return new JsonResponse(['msg' => 'Success'], 200);
    }

    public function cats_delete(Request $request)
    {
        $input = $request->all();
        $cats = GamesCats::query()->where('id', $input['category_id'])->first();
        if ($cats->slug_suffix) {
            GameCategoryBinds::query()->where('category_id', $input['category_id'])->delete();
            $cats->delete();
        }

        return new JsonResponse(['msg' => 'Success'], 200);
    }

    public function cats_single_save(Request $request)
    {

        $result = $request->all();

        if (!$result['new']) {
            $id = $result['id'];
            GamesCats::query()->where('id', $id)->update([
                'provider_id' => $result['provider_id'],
                'title' => $result['name'],
                'desktop' => $result['desktop'],
                'mobile' => $result['mobile'],
                'enabled' => $result['enabled'],
                'slug' => $result['slug'],
                'slug_suffix' => $result['slug_suffix'],
                'suffix_name' => $result['suffix_name'],
                'weight' => $result['weight'],
            ]);
            $response = ['msg' => 'Success'];

        } else {
            $id = GamesCats::query()->insertGetId([
                'provider_id' => $result['provider_id'],
                'desktop' => $result['desktop'],
                'mobile' => $result['mobile'],
                'title' => $result['name'],
                'enabled' => $result['enabled'],
                'slug' => $result['slug'],
                'slug_suffix' => $result['slug_suffix'],
                'suffix_name' => $result['suffix_name'],
                'weight' => $result['weight'],
            ]);
            $response = ['msg' => 'Success', 'id' => $id];
        }

        $included_game = array_map(function ($value) use ($id) {
            return [
                'game_id' => $value['id'],
                'category_id' => $id,
                'excluded' => 0,
                'weight' => $value['weight']
            ];
        }, $result['included_game']);


        GameCategoryBinds::query()->where('category_id', $id)->delete();
        GameCategoryBinds::query()->insert($included_game);

        return new JsonResponse($response, 200);
    }

}
