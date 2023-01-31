<?php

namespace App\Http\Resources\Players;

use Illuminate\Http\Resources\Json\JsonResource;

class PlayerResource extends JsonResource
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
            'email' => $this->email,
            'firstname' => $this->firstname,
            'lastname' => $this->lastname,
            'fullname' => $this->fullname,
            'country_id' => $this->country_id,
            'city' => $this->city,
            'gender' => $this->gender,
            'promo_sms' => $this->promo_sms,
            'promo_email' => $this->promo_email,
            'dob' => $this->dob,
            'phone' => $this->phone,
            'postal_code' => $this->postal_code,
            'wallets' => \App\Http\Resources\Players\WalletsResource::collection($this->wallets),
            'bonuses' => \App\Http\Resources\Players\BonusIssueResource::collection($this->bonus_issue()->where("status", "=", 2)->get())
        ];
    }
}
