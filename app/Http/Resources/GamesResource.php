<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class GamesResource extends JsonResource
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
            'img' => $this->img,
            'uri' => $this->uri,
            'name' => $this->name,
            'provider' => $this->provider,
        ];
    }
}
