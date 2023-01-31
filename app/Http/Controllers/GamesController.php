<?php

namespace App\Http\Controllers;

use App\Http\Resources\GameCatsResource;
use App\Http\Resources\GamesResource;
use App\Http\Resources\GamesSortingResource;
use App\Http\Resources\WinnersResource;
use App\Models\Games;
use App\Models\GamesBets;
use App\Models\GamesCats;
use App\Models\GamesProvider;
use App\Models\GamesSorting;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use App\Models\Game;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class GamesController extends Controller
{
    public $game;
    private $input;

    public function getGames(Request $request, Games $games)
    {
        $this->input = $request->all();

        $this->common_data = \Request::get('common_data');

            if (isset($this->input['category_id']) && $this->input['category_id'] !== 0) {

                $cats = GamesCats::find($this->input['category_id']);

                return GamesResource::collection($cats->games()
                    ->when(request('currency_id', false), function ($query, $currency_id) {
                        $query->whereHas('currency', function ($query) use ($currency_id) {
                            $query->where('currency_id', $currency_id);
                        });
                    })
                    ->whereHas('provider', function ($query){
                        $query->where('enabled', 1);
                    })
                    ->orderBy('game_category_binds.weight', 'asc')->paginate(28));
            }

            if (isset($this->input['provider'])) {
                $provider = GamesProvider::query()->where('code', $this->input['provider'])->first();
                return GamesResource::collection($provider->games()
                    ->when(request('currency_id', false), function ($query, $currency_id) {
                        $query->whereHas('currency', function ($query) use ($currency_id) {
                            $query->where('currency_id', $currency_id);
                        });
                    })
                    ->whereHas('provider', function ($query){
                        $query->where('enabled', 1);
                    })
                    ->where('published', 1)
                    ->paginate(28));
            }


            return GamesSortingResource::collection(GamesSorting::with('games')
                ->whereHas('games', function ($query) {
                    $query->when(request('currency_id', false), function ($q, $currency_id) {
                        $q->whereHas('currency', function ($query) use ($currency_id) {
                            $query->where('currency_id', (int)$currency_id);
                        });
                    });
                    $query->when(request('search', false), function ($query, $search) {
                        $query->where('name', 'like', '%' . $search . '%');
                    });
                    $query->whereHas('provider', function ($query){
                        $query->where('enabled', 1);
                    });
                })->orderBy('weight', 'asc')
                ->paginate(28));

//        if ($validator->passes()) {
//
//            if(isset($this->input['category_id']) && $this->input['category_id'] !== 0){
//                $cats = GamesCats::find($this->input['category_id']);
//                return GamesResource::collection($cats->games()
//                    ->when(request('currency_id', false), function ($query, $currency_id){
//                        $query->whereHas('currency', function ($query) use ($currency_id){
//                            $query->where('currency_id', $currency_id);
//                        });
//                    })
//                    ->orderBy('game_category_binds.weight', 'asc')->paginate(28));
//            }
//
//            return GamesResource::collection(Games::with(['category' => function ($query) {
//                $query->orderBy('game_category_binds.weight', 'asc');
//            }, 'sorting' => function ($query) {
//                $query->orderBy('games_sorting.weight', 'asc');
//            }, 'provider'])
//                ->when(request('search', false), function ($query, $search){
//                    $query->where('name', 'like', '%' . $search . '%');
//                })
//                ->when(!request('category_id', false) && !request('provider', false), function ($q){
//                    $q->whereHas('sorting');
//                    $q->join('games_sorting', 'games.id', '=', 'games_sorting.game_id')
//                        ->join('game_provider', 'games.provider_id', '=', 'game_provider.id')
//                        ->where('game_provider.enabled', 1)
//                        ->orderBy('games_sorting.weight', 'ASC');
//                })
//                ->when(request('currency_id', false), function ($q, $currency_id){
//                    $q->whereHas('currency', function ($query) use ($currency_id){
//                        $query->where('currency_id', (int)$currency_id);
//                    });
//                })
//                ->when(request('category_id', false), function ($q, $category_id){
//                    $q->whereHas('category', function ($query) use ($category_id){
//                        $query->where('category_id', (int)$category_id);
//                    });
//                    $q->join('game_category_binds', 'games.id', '=', 'game_category_binds.game_id')
//                        ->orderBy('game_category_binds.weight', 'ASC');
//                })
//                ->when(request('provider', false), function ($q, $provider){
//                    $q->whereHas('provider', function ($query) use ($provider){
//                        $query->where('code', $provider);
//                    });
//                })
//                ->groupBy('games.id')
//               ->paginate(28));
//        }

    }


    public function games_by_main(Request $request)
    {
        $input = $request->all();

        $this->common_data = \Request::get('common_data');

        $validator = Validator::make($input, [
            'page' => 'required',
        ]);

        if ($validator->passes()) {
            $this->game = new Game;

            $games = $this->game->get_games($input['page'], $input['search'] ?? false);
            return response()->json(['success' => 1, 'games' => $games]);
        }
        return response()->json(['errors' => $validator->errors()->all(), 'error_keys' => array_keys($validator->errors()->messages())]);
    }

    public function games_by_category(Request $request)
    {
        $input = $request->all();

        $this->common_data = \Request::get('common_data');

        $validator = Validator::make($input, [
            'page' => 'required',
            'id' => 'required',
        ]);

        if ($validator->passes()) {
            $this->game = new Game;

            $games = $this->game->get_games_by_category_id($input['page'], $input['id'], $input['search'] ?? false);
            return response()->json(['success' => 1, 'games' => $games]);
        }
        return response()->json(['errors' => $validator->errors()->all(), 'error_keys' => array_keys($validator->errors()->messages())]);
    }

    public function games_by_played(Request $request)
    {
        $input = $request->all();

        $this->common_data = \Request::get('common_data');

        $validator = Validator::make($input, [
            'page' => 'required',
        ]);

        if (!$this->common_data['user']->id) {
            return response()->json(['errors' => 1, 'error_keys' => 'User']);
        }

        if ($validator->passes()) {
            $this->game = new Game;

            $games = $this->game->get_game_played($this->common_data['user']->id, $input['page'], $input['search'] ?? false);
            return response()->json(['success' => 1, 'games' => $games]);
        }

        return response()->json(['errors' => $validator->errors()->all(), 'error_keys' => array_keys($validator->errors()->messages())]);
    }


    public function game_by_provider(Request $request)
    {
        $input = $request->all();

        $this->common_data = \Request::get('common_data');

        $validator = Validator::make($input, [
            'page' => 'required',
            'slug' => 'required',
        ]);

        if ($validator->passes()) {
            $this->game = new Game;

            $games = $this->game->get_games_provider($input['slug'], $input['page']);
            return response()->json(['success' => 1, 'games' => $games]);
        }

        return response()->json(['errors' => $validator->errors()->all(), 'error_keys' => array_keys($validator->errors()->messages())]);
    }

    public function game_by_new(Request $request)
    {
        $input = $request->all();

        $this->common_data = \Request::get('common_data');

        $validator = Validator::make($input, [
            'page' => 'required',
        ]);

        if ($validator->passes()) {
            $this->game = new Game;

            $games = $this->game->get_games_by_new($input['page'], $input['search'] ?? false);
            return response()->json(['success' => 1, 'games' => $games]);
        }

        return response()->json(['errors' => $validator->errors()->all(), 'error_keys' => array_keys($validator->errors()->messages())]);
    }

    public function game_by_search(Request $request)
    {
        $input = $request->all();

        $this->common_data = \Request::get('common_data');

        $validator = Validator::make($input, [
            'search' => 'required',
        ]);

        if ($validator->passes()) {
            $this->game = new Game;

            $games = $this->game->get_games_by_options($input['search']);
            return response()->json(['success' => 1, 'games' => $games]);
        }

        return response()->json(['errors' => $validator->errors()->all(), 'error_keys' => array_keys($validator->errors()->messages())]);
    }

    public function activeProvider(Request $request){
        return GamesProvider::query()
            ->select('game_provider.*', 'producers_sorting.weight')
            ->leftJoin('producers_sorting', 'game_provider.id', '=', 'producers_sorting.producer_id')
            ->Join('games', 'game_provider.id', '=', 'games.provider_id')
            ->join('game_currency', 'games.id', '=', 'game_currency.game_id')
            ->when(request('currency_id', false), function ($query, $currency_id){
                $query->where('game_currency.currency_id', $currency_id);
            })
            ->where('game_provider.enabled', 1)
            ->orderByRaw(DB::raw('ISNULL(producers_sorting.weight), producers_sorting.weight ASC'))
            ->groupBy('game_provider.id')
            ->get();
    }


    public function winnersBet(){
        return WinnersResource::collection(GamesBets::query()
            ->where('profit', '>', 3)
            ->limit(50)
            ->orderBy('id', 'DESC')
            ->groupBy('user_id')
            ->get());
    }

}
