<?php

namespace App\Http\Resources\Players;

use Illuminate\Http\Resources\Json\JsonResource;

class BonusIssueResource extends JsonResource
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
            'wagered' => $this->wagered,
            'to_wager' => $this->to_wager,
            'locked_amount' => $this->locked_amount,
            'fixed_amount' => $this->fixed_amount,
            'amount' => $this->amount,
            'currency_id' => $this->currency_id,
            'cat_type' => $this->cat_type,
        ];
    }
}
