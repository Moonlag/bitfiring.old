<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\App;
use Str;

class LocaleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $locale = [];
        foreach (\App\Models\Languages::all() as $lang){
            $locale[$lang->id] = $lang->translation()->where('code', $this->code)->where('language_id', $lang->id)->first();
            $locale[$lang->id]['change'] = false;
        }

        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'locale' =>  (object)$locale,
            'title' =>  $this->title,
            'code' =>  $this->code,
            'active' =>  $this->active,
            'save' =>  true,
            'change' => false,
        ];
    }
}
