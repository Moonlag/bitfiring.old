<?php

namespace App\Orchid\Screens\Games;

use App\Http\Resources\DenominationResource;
use App\Models\GamesProvider;
use Illuminate\Support\Facades\DB;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;

class Denomination extends Screen
{
    /**
     * Display header name.
     *
     * @var string
     */
    public $name = 'Denomination';

    /**
     * Query data.
     *
     * @return array
     */
    public function query(): array
    {

        return [
            'common_data' => DenominationResource::collection(GamesProvider::with('currency')->where('enabled', 1)->get())
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
            Layout::view('orchid.games.denomination_filter')
        ];
    }

    public function save(\Illuminate\Http\Request $request){
       $input = $request->all();

       foreach ($input['data'] as $row){
           DB::table('provider_currency_map')
               ->where([
                   ['provider_id', '=', $row['ids']['provider_id']],
                   ['currency_id', '=', $row['ids']['currency_id']]
               ])->update(['denomination' => $row['denomination']]);

           $rd = DB::table('provider_currency_map')
               ->where([
                   ['provider_id', '=', $row['ids']['provider_id']],
                   ['currency_id', '=', $row['ids']['currency_id']]
               ])->get();

       }
    }
}
