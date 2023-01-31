<?php

namespace App\Models;

use App\Orchid\Screens\Marketing\GamesSessions;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;

class Game extends Model
{

    public function get_game_by_id($id)
    {
        return DB::table('games')
            ->where('id', $id)
            ->get()->first();
    }

    public function get_game_by_identer($identer)
    {
        return DB::table('games')
            ->select('*')
            ->where('identer', '=', $identer)
            ->get()->first();
    }

    public function get_game_by_slug($game_slug)
    {
        return DB::table('games')
            ->leftJoin('game_provider', 'games.provider_id', '=', 'game_provider.id')
            ->where('game_provider.enabled', 1)
            ->select('games.*')
            ->where('uri', '=', $game_slug)
            ->get()->first();
    }

    public function get_game_by_play($game_slug, $provider_code)
    {
        return DB::table('games')
            ->leftJoin('game_provider', 'games.provider_id', '=', 'game_provider.id')
            ->where('game_provider.enabled', 1)
            ->select('games.*')
            ->where('uri', '=', $game_slug)
            ->where('game_provider.code', '=', $provider_code)
            ->get()->first();
    }

    public function get_game_by_currency($game_id, $currency_id)
    {
        return DB::table('game_currency')->where([['game_id', '=', $game_id], ['currency_id', '=', $currency_id]])->first();

    }

    public function get_game_type_by_id($id)
    {

        return DB::table('games_cats')
            ->where('id', $id)
            ->get()->first()->title;

    }

    public function get_game_id_by_type($type)
    {

        return DB::table('game_types')
            ->select('game_type_id')
            ->where('name', '=', $type)
            ->get()->first();

    }

    public function get_game($category_id, $game_slug)
    {

        return DB::table('games')
            ->where('category_id', '=', $category_id)
            ->where('uri', '=', $game_slug)
            ->get()->first();

    }

    public function get_game_sessions()
    {

        return DB::table('game_sessions')
            ->select(['game_sessions.*', 'players.email'])
            ->join('players', 'players.id', '=', 'game_sessions.user_id')
            ->orderBy('game_sessions.id', 'DESC')
            ->get()->toArray();

    }

    public function get_game_provider()
    {
        return DB::table('game_provider')
            ->leftJoin('producers_sorting', 'game_provider.id', '=', 'producers_sorting.producer_id')
            ->select('game_provider.*', 'producers_sorting.weight')
            ->where('game_provider.enabled', 1)
            ->orderByRaw(DB::raw('ISNULL(producers_sorting.weight), producers_sorting.weight ASC'))
            ->get();
    }



    public function get_games($page = 1, $search = false)
    {

        $games = DB::table('games')
            ->leftJoin('games_sorting', 'games.id', '=', 'games_sorting.game_id')
            ->leftJoin('game_provider', 'games.provider_id', '=', 'game_provider.id')
            ->select(['game_provider.id as provider_id', DB::raw('coalesce(weight,10000) as true_weight'), 'games.id', 'games.img', 'games.uri', 'games.popular', 'games.studios', 'games.races', 'games.bonus_enabled', 'games.jp', 'games.name'])
            ->where('games.published', 1)
            ->where('game_provider.enabled', 1)
            ->orderBy('true_weight', 'ASC')
            ->orderBy('games.id', 'DESC');

        if ($search) {
            $games = $games->where('games.name', 'like', '%' . $search . '%');
        }

        return $games->paginate(28, '', '', $page);

    }

    public function get_games_by_category_id($page = 1, $category_id = 1, $search = false, $provider = false)
    {
        $games = DB::table('games')
            ->leftJoin('game_provider', 'games.provider_id', '=', 'game_provider.id')
            ->leftJoin('game_category_binds', 'games.id', 'game_category_binds.game_id')
            ->select(['game_provider.id as provider_id', DB::raw('coalesce(weight,10000) as true_weight'), 'games.id', 'games.img', 'games.uri', 'games.popular', 'games.studios', 'games.races', 'games.bonus_enabled', 'games.jp', 'games.name'])
            ->where('games.published', 1)
            ->where('game_provider.enabled', 1)
            ->where('game_category_binds.category_id', '=', $category_id)
            ->orderBy('true_weight', 'ASC')
            ->orderBy('games.id', 'DESC');

        if ($search) {
            $games = $games->where('games.name', 'like', '%' . $search . '%');
        }

        return $games->paginate(28, '', '', $page);
    }


    public function get_games_provider($slug, $page)
    {
        return DB::table('games')
            ->leftJoin('game_provider', 'games.provider_id', '=', 'game_provider.id')
            ->select(['game_provider.id as provider_id', 'games.id', 'games.img', 'games.uri', 'games.popular', 'games.studios', 'games.races', 'games.bonus_enabled', 'games.jp', 'games.name'])
            ->where('games.published', 1)
            ->where('game_provider.enabled', 1)
            ->where('game_provider.code', $slug)
            ->paginate(28, '', '', $page);
    }


    public function get_game_played($user_id, $page, $search = false)
    {
        $games = DB::table('game_sessions as gs1')
            ->select('games.id',
                'game_provider.id as provider_id', 'games.img', 'games.uri', 'games.popular', 'games.studios', 'games.races', 'games.bonus_enabled', 'games.jp', 'games.name',
                DB::raw('MAX(gs1.id) as session_id'))
            ->join('game_sessions as gs2', function ($join) use ($user_id) {
                $join->select(DB::raw('MAX(gs2.id) as id'), 'gs2.game_id')
                    ->whereRaw(DB::raw("gs2.user_id = $user_id"))
                    ->on('gs1.id', '=', 'gs2.id');
                $join->groupBy('gs2.game_id');
            })->Join('games', function ($join) {
                $join->on('gs1.game_id', '=', 'games.id');
            })->Join('game_provider', function ($join) {
                $join->on('games.provider_id', '=', 'game_provider.id');
            })
            ->where('gs1.user_id', $user_id)
            ->where('games.published', 1)
            ->where('game_provider.enabled', 1);
        if ($search) {
            $games = $games->where('games.name', 'like', '%' . $search . '%');
        }
        return $games->orderBy('session_id', 'DESC')
            ->groupBy('gs1.game_id')
            ->paginate(28, '', '', $page);
    }

    public function get_games_by_search($search)
    {
        return DB::table('games')
            ->leftJoin('game_provider', 'games.provider_id', '=', 'game_provider.id')
            ->select(['game_provider.id as provider_id', 'games.id', 'games.img', 'games.uri', 'games.popular', 'games.studios', 'games.races', 'games.bonus_enabled', 'games.jp', 'games.name'])
            ->where('games.published', 1)
            ->where('game_provider.enabled', 1)
            ->orderBy('games.name', 'ASC')
            ->orderBy('games.id', 'DESC')
            ->where('games.name', 'like', '%' . $search . '%')
            ->get();
    }


    public function get_games_by_new($page, $search = false)
    {
        $games =  DB::table('games')
            ->leftJoin('game_provider', 'games.provider_id', '=', 'game_provider.id')
            ->select(['game_provider.id as provider_id', 'games.id', 'games.img', 'games.uri', 'games.popular', 'games.studios', 'games.races', 'games.bonus_enabled', 'games.jp', 'games.name'])
            ->where('games.published', 1)
            ->where('game_provider.enabled', 1);
        if ($search) {
            $games = $games->where('games.name', 'like', '%' . $search . '%');
        }

        return $games->orderBy('games.id', 'DESC')
            ->paginate(28, '', '', $page);
    }

}
