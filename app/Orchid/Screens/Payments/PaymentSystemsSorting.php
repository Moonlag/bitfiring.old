<?php

namespace App\Orchid\Screens\Payments;

use App\Models\Countries;
use App\Models\PaymentSystem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;

class PaymentSystemsSorting extends Screen
{
//    /**
//     * Display header name.
//     *
//     * @var string
//     */
//    public $name = 'PaymentSystemsSorting';
//
//    /**
//     * Display header description.
//     *
//     * @var string|null
//     */
//    public $description = 'PaymentSystemsSorting';

    public $permission = [
        'platform.payments.systems.sorting'
    ];


    /**
     * Query data.
     *
     * @return array
     */
    public function query(): array
    {
        $ps = PaymentSystem::query()->select('id', 'name')->get();
        $country = Countries::query()->select('id', 'name')->get();
        $global = \App\Models\PaymentSystem::query()->select('name', 'sorting', 'id')->whereNotNull('sorting')->get();
        $regional = \App\Models\PaymentSystemsSorting::query()->whereNotNull('sorting_id')->orderBy('sorting_id', 'ASC')->get();
        return [
            'common_data' => [
                'p_system' => $ps,
                'country' => $country,
                'global' => $global,
                'regional' => $regional
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
            Layout::view('orchid.payment.systems_sorting')
        ];
    }

    public function create_regional(Request $request){
        $id = \App\Models\PaymentSystemsSorting::query()->select('id')->latest('id')->first()->id ?? 0;
        $id += 1;
        $country = $request->data['country'];
        $ps = $request->data['payment_system'];
        $data = [];
        foreach ($ps as $p){
            foreach ($country as $c){
                $data[] = [
                    'country_id' => $c['id'] ?? null,
                    'ps_id' => $p['id'] ?? null,
                    'sorting_id' => $id,
                    'sorting' => $p['sorting'] ?? null
                ];
            }
        }

        \App\Models\PaymentSystemsSorting::query()->insert($data);
        return response()->json(['id' => $id], 200);
    }

    public function sorting_global(Request $request){
        \App\Models\PaymentSystem::query()->update(['sorting' => null]);
        $this->create_global($request);
    }

    public function sorting_regional(Request $request){
        $id = $request->data['id'];
        $country = $request->data['country'];
        $ps = $request->data['payment_system'];
        \App\Models\PaymentSystemsSorting::query()->where('sorting_id', $id)->delete();

        $data = [];
        foreach ($ps as $p){
            foreach ($country as $c){
                $data[] = [
                    'country_id' => $c['id'] ?? null,
                    'ps_id' => $p['id'] ?? null,
                    'sorting_id' => $id,
                    'sorting' => $p['sorting'] ?? null
                ];
            }
        }
        \App\Models\PaymentSystemsSorting::query()->insert($data);
        return response()->json(['id' => $id], 200);
    }

    public function create_global(Request $request){
        foreach ($request->data as $ps){
            \App\Models\PaymentSystem::query()->where('id', $ps['id'])->update(['sorting' => $ps['sorting']]);
        }
    }

    public function remove(Request $request){
        $id = $request->data;
        \App\Models\PaymentSystemsSorting::query()->where('sorting_id', $id)->delete();
    }

    public function close_global(Request $request){
        $id = $request->data;
        \App\Models\PaymentSystem::query()->where('id', $id)->update(['sorting' => null]);
    }

    public function close_regional(Request $request){
        $id = $request->id;
        $ps_id = $request->ps_id;
        \App\Models\PaymentSystemsSorting::query()->where('sorting_id', $id)->where('ps_id', $ps_id)->delete();
    }
}
