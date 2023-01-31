<?php

namespace App\Http\Resources\Players;

use Illuminate\Http\Resources\Json\JsonResource;

class WalletsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {

        return [
            'id' => $this->id,
            'balance' => $this->balance,
            'primary' => $this->primary,
            'code' => $this->currency->code,
            'currency_id' => $this->currency->id,
        ];
    }
}
