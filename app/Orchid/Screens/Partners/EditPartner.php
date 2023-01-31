<?php

namespace App\Orchid\Screens\Partners;

use App\Models\Partner;
use App\Models\TagItem;
use App\Models\Tags;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\TextArea;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Screen;
use Orchid\Screen\Actions\Link;
use Orchid\Support\Facades\Alert;

class EditPartner extends Screen
{
    /**
     * Display header name.
     *
     * @var string
     */
    public $name = 'EditPartner';
    public $exist = false;
    public $type;
    public $id;
    /**
     * Display header description.
     *
     * @var string
     */
    public $description = 'EditPartner';
    public $tags;

    /**
     * Query data.
     *
     * @return array
     */
    public function query(Partner $model): array
    {
        $this->exist = $model->exists;
        if ($this->exist) {
            $this->name = $model->email;
            $this->description = 'id: ' . $model->id;
            $this->type = $model->id_type;
            $this->id = $model->id;
        }


        $tags_partner = Tags::query()
            ->leftJoin('tag_item', 'tags.id', '=', 'tag_item.tag_id')
            ->where('item_id', $model->id)
            ->select('tag_item.tag_id')
            ->get()->toArray();

        $this->tags = array_column($tags_partner, 'tag_id');

        return [
            'partner' => $model,
        ];
    }

    /**
     * Button commands.
     *
     * @return Action[]
     */
    public function commandBar(): array
    {
        return [
            Button::make('Save')
                ->icon('check')
                ->method('partner_update')
                ->parameters([
                    'partner_id' => $this->id,
                    'tags_value' => $this->tags
                ]),
            Link::make('Cancel')
                ->icon('left')
                ->route('platform.partners.view', $this->id),
        ];
    }

    /**
     * Views.
     *
     * @return Layout[]
     */
    public function layout(): array
    {

        return [
            Layout::rows([ Input::make('partner.company')
                ->title('Company name')
                ->canSee($this->type === 2)
                ->horizontal(),

                Input::make('partner.firstname')
                    ->title('Firstname')
                    ->canSee($this->type === 1)
                    ->horizontal(),

                Input::make('partner.lastname')
                    ->title('Lastname')
                    ->canSee($this->type === 1)
                    ->horizontal(),

                Input::make('partner.address')
                    ->title('Address')
                    ->horizontal(),

                Input::make('partner.phone')
                    ->title('Phone')
                    ->horizontal(),

                TextArea::make('partner.traffic')
                    ->rows(7)
                    ->title('Traffic source')

                    ->horizontal(),

                Select::make('tags')
                    ->title('Tags')
                    ->taggable()
                    ->multiple()
                    ->fromModel(Tags::class, 'name')
                    ->value($this->tags)->autocomplete(true)
                    ->horizontal()
            ]),
        ];
    }

    public function partner_update(Request $request)
    {


        try {
            $this->validate($request, [
                'partner.phone' => ['required']
            ]);
            Partner::where('id', $request->partner_id)->update(
                array_filter($request->partner)
            );

            if (!empty($request->tags_value)) {
                TagItem::query()->where('item_id', $request->partner_id)->delete();
            }


            if (!empty($request->tags)) {
                $tags_type = [];

                foreach ($request->tags as $kye => $value) {
                    $value = (int)$value === 0 ? $value : (int)$value;
                    if (is_int($value)) {
                        $tags_type[] = [
                            'section_id' => 1,
                            'item_id' => (int)$request->partner_id,
                            'tag_id' => $value,
                        ];
                        continue;
                    }

                    $tags = [
                        'name' => $value,
                        'slug' => Str::slug($value),
                        'section_id' => 1,
                    ];
                    $id = Tags::insertGetId(
                        $tags
                    );
                    $tags_type[] = [
                        'section_id' => 1,
                        'item_id' => (int)$request->partner_id,
                        'tag_id' => $id,
                    ];
                }


                TagItem::query()->insert($tags_type);
                Alert::info('You have successfully.');
            }
            Alert::info('You have successfully update partner profile.');
        } catch (ValidationException $e) {
            $msg = '';
            foreach ($e->errors() as $key => $value) {
                $msg .= join('', $value);
            }
            Alert::warning($msg);
            return redirect()->route('platform.partners.view', $request->partner_id);
        }
        return redirect()->route('platform.partners.view', $request->partner_id);
    }
}
