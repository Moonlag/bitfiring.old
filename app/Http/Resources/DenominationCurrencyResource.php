<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DenominationCurrencyResource extends JsonResource
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
            'ids' => $this->pivot,
            'code' => $this->code,
            'denomination' => $this->denomination,
        ];
    }
}
