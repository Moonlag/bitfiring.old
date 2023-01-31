<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use Session;

class LocaleApi extends Controller
{
    public function loadLocaleTranslate(Request $request)
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'locale' => 'required',
        ]);

        if ($validator->passes()) {

            $translate = [];

            $language = \App\Models\Languages::query()->where('code', $input['locale'])->first();

            $phrase_translate = \App\Models\PhraseTranslations::query()
                ->where('language_id', '=', $language->id)
                ->get();

            $block_translate = \App\Models\BlockTranslations::query()
                ->select('href as code', 'description as text', 'language_id', 'id', 'url')
                ->where('language_id', $language->id)
                ->orderBy('id', 'DESC')
                ->get();

            foreach ($phrase_translate as $item){
                $translate[$item->code] = $item->text;
            }

            foreach ($block_translate as $item){
                if(isset($translate['block'][$item->code])){

                    if(!is_array($translate['block'][$item->code])){
                        $translate['block'][$item->code] = [$translate['block'][$item->code]];
                    }

                    $translate['block'][$item->code] = [...$translate['block'][$item->code], $item];
                    continue;
                }
                $translate['block'][$item->code] = $item;
            }

            Session::push('locale', $input['locale']);

            return response()->json($translate);
        }
    }
}
