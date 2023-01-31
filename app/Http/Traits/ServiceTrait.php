<?php

namespace App\Http\Traits;

use DB;
use Eloquent;
use Carbon\Carbon;

trait ServiceTrait
{

    public function get_denomination($currency_id, $provider_id)
    {
		
		$denomination = DB::table('provider_currency_map')->select('denomination','altercode','decimals')->where('currency_id', '=', $currency_id)->where('provider_id', '=', $provider_id)->get();
		
		if (isset($denomination[0])) {
            return $denomination[0];
        }

        return 0;
		
    }

}
