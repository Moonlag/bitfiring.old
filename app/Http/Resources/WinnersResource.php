<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Str;
class WinnersResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $name = Str::before($this->player->email, '@');
        $length = Str::of($name)->length();
        $trim = $length * 0.5;

        return [
            'id' => $this->id,
            'amount' => abs((float)$this->profit) / (float)$this->wallets->currency->rate,
            'game' => new GamesResource($this->games),
            'player' =>  Str::padRight(Str::of($name)->substr(0, $length - $trim), $length, '.'),
        ];
    }
}
