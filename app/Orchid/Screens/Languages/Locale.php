<?php

namespace App\Orchid\Screens\Languages;

use Illuminate\Http\Request;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;

class Locale extends Screen
{
    /**
     * Display header name.
     *
     * @var string
     */
    public $name = 'Locale';

    /**
     * Query data.
     *
     * @return array
     */
    public function query(): array
    {
        $locale = \App\Models\Languages::all();
        $phrase_translations = $this->load(\request());

        return [
            'locale' => $locale,
            'phrase_translations' => $phrase_translations,
        ];
    }

    /**
     * Button commands.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): array
    {
        return [];
    }

    /**
     * Views.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    public function layout(): array
    {
        return [
            Layout::view('orchid.lang.language')
        ];
    }

    public function save(Request $request)
    {


        foreach ($request->all() as $item) {
            \App\Models\PhraseTranslations::updateOrCreate(
                ['uuid' => $item['uuid'], 'language_id' => $item['language_id']],
                ['text' => $item['text'], 'active' => $item['active'], 'title' => $item['title'], 'code' => $item['code']]
            );
        }

        return response()->json(['success' => 1]);
    }

    public function load(Request $request)
    {
        return \App\Http\Resources\LocaleResource::collection(\App\Models\PhraseTranslations::query()
            ->groupBy('code')
            ->orderBy('id', 'ASC')
            ->offset($request->get('offset', 0))
            ->limit($request->get('limit', 20))
            ->get());
    }

    public function remove(Request $request)
    {

        \App\Models\PhraseTranslations::where('uuid', $request->get('uuid'))->delete();

        return response()->json(['success' => 1]);
    }
}
