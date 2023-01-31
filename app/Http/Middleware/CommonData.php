<?php

namespace App\Http\Middleware;

use Closure;
use Corcel;
use Jenssegers\Agent\Agent;
use LaravelLocalization;
use App\Http\Traits\ServiceTrait;
use App\Http\Traits\LanguageTrait;
use App\Http\Traits\AuthTrait;
use App\Http\Traits\DBTrait;
use App\Http\Traits\RoutineTrait;
use App\Http\Traits\PaymentTrait;
use Auth;

class CommonData
{
	/*
	use AuthTrait;
	use LanguageTrait;
	use ServiceTrait;
	use RoutineTrait;
	use PaymentTrait;
	*/

	use DBTrait;


    public function handle($request, Closure $next)
    {

		$common_data = [];
        $common_data['user'] = Auth::guard('clients')->user();
		if(isset($common_data['user']->id)){
            $common_data['user'] = new \App\Http\Resources\Players\PlayerResource($common_data['user']);
        }


		$common_data['request_type'] = 'common';

		$request->attributes->add(["common_data" => $common_data]);

        $this->watchdog([
            'ip' => $request->ip(),
            'player_id' => $common_data['user']->id ?? null,
            'uri' => $request->path(),
            'body' =>  json_encode($request->all()) . file_get_contents('php://input'),
            'headers' =>  json_encode($request->header())
        ]);

		return $next($request);
    }
}

