<?php

namespace App\Orchid\Screens\Bonuses;

use App\Models\Currency;
use App\Models\FreespinBonusGamesModel;
use App\Models\Games;
use App\Models\GamesProvider;
use App\Models\IssuePlayerLimit;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Exception\NotReadableException;
use Intervention\Image\ImageManagerStatic as Image;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;

class NewBonuses extends Screen
{
    /**
     * Display header name.
     *
     * @var string
     */
    public $name = 'NewBonuses';

    public $bonus_id;
    public $image;
    /**
     * Display header description.
     *
     * @var string|null
     */
    public $description = 'NewBonuses';

    /**
     * Query data.
     *
     * @return array
     */
    public function query(): array
    {
        return [
            'common_data' => [
                'currency' => Currency::query()->where('parent_id', '=', 0)->get(),
                'provider' => GamesProvider::query()->get(),
                'freespins' => \App\Models\FreespinBonusModel::query()->get()
            ]
        ];
    }

    /**
     * Button commands.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): array
    {
        return [
            Link::make('Return')
                ->class('btn btn-outline-secondary mb-2')
                ->icon('left')
                ->route('platform.bonuses'),
        ];
    }

    /**
     * Views.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    public function layout(): array
    {
        return [
            Layout::view('orchid.bonuses.bonuses'),
        ];
    }

    public function save(Request $request): JsonResponse
    {
        $input = $request->all();
        $data = json_decode($input['data']);
        $image = $input['image'];
       
        if ($image) {
            $this->image = $this->upload_image($image, $data->data->value[0]->model->title);
        }

        switch ($data->data->id) {
            case 1:
                return $this->first_deposit($data->data->value, $data->currency, $image);
            case 2:
                return $this->n_deposit($data->data->value, $data->currency);
            case 3:
                return $this->no_deposit($data->data->value, $data->currency);
            case 4:
                return $this->freespin($data->data->value, $data->currency);
            default:
                return new JsonResponse(['msg' => 'Error'], 403);
        }

    }

    public function get_game(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return new JsonResponse(['error' => 'Invalid request'], 400);
        }

        $games = Games::query()->select('id', 'name')->where('provider_id', $request->id)->get();

        return new JsonResponse($games, 200);
    }

    public function first_deposit($data, $currency, $image): JsonResponse
    {
        $data = (array)$data;
        $data = array_column($data, 'model');

        $query = [
            'title' => $data[0]->title,
            'title_frontend' => $data[0]->title_frontend,
            'description' => $data[0]->description,
            'mini_description' => $data[0]->mini_description,
            'code' => $data[0]->code,
            'currency_id' => $currency,
            'freespin_id' => $data[0]->freespin_id,
            'status' => $data[0]->enabled,
            'percentage' => $data[1]->percentage,
            'amount' => $data[1]->amount,
            'wager' => $data[1]->wager,
            'cashable' => $data[1]->cashable,
            'duration' => $data[2]->duration,
            'duration_type' => $data[2]->type,
            'min' => $data[3]->min,
            'max' => $data[3]->max,
            'strategy_id' => 3,
            'type_id' => 1,
            'image' => $this->image
        ];

        $validator = Validator::make($query, [
            'title' => 'required',
            'description' => 'required',
            'currency_id' => 'required',
        ]);

        if ($validator->passes()) {
            if ($image) {
                $path = $this->upload_image($image, $data[0]->title);
                $query['image'] = $path;
            }

            \App\Models\Bonuses::query()->insert($query);

            return new JsonResponse(['success' => 1, 'msg' => 'Success'], 200);
        }

        return response()->json(['errors' => $validator->errors()->all(), 'error_keys' => array_keys($validator->errors()->messages())]);
    }

    public function n_deposit($data, $currency): JsonResponse
    {
        $data = (array)$data;
        $data = array_column($data, 'model');

        $query = [
            'title' => $data[0]->title,
            'title_frontend' => $data[0]->title_frontend,
            'description' => $data[0]->description,
            'mini_description' => $data[0]->mini_description,
            'code' => $data[0]->code,
            'freespin_id' => $data[0]->freespin_id,
            'currency_id' => $currency,
            'strategy_id' => 3,
            'idx' => $data[0]->index,
            'once_per_day' => $data[0]->once_per_day,
            'type_id' => 2,
            'status' => $data[0]->enabled,
            'percentage' => $data[1]->percentage,
            'amount' => $data[1]->amount,
            'wager' => $data[1]->wager,
            'cashable' => $data[1]->cashable,
            'duration' => $data[2]->duration,
            'duration_type' => $data[2]->type,
            'min' => $data[3]->min,
            'max' => $data[3]->max,
            'image' => $this->image
        ];


        $this->bonus_id = \App\Models\Bonuses::query()->insertGetId($query);


        $query = [];
        foreach ($data[5]->limits as $limit) {
            $query[] = [
                'bonus_id' => $this->bonus_id,
                'limit_amount' => $limit->more,
                'time_amount' => $limit->total,
                'time_type' => $limit->type,
            ];
        }
        IssuePlayerLimit::query()->insert($query);
        return new JsonResponse(['success' => 1, 'msg' => 'Success'], 200);
    }

    public function no_deposit($data, $currency, $image): JsonResponse
    {
        $data = (array)$data;
        $data = array_column($data, 'model');
        $query = [
            'title' => $data[0]->title,
            'title_frontend' => $data[0]->title_frontend,
            'description' => $data[0]->description,
            'mini_description' => $data[0]->mini_description,
            'code' => $data[0]->code,
            'currency_id' => $currency,
            'status' => $data[0]->enabled,
            'percentage' => $data[1]->percentage,
            'amount' => $data[1]->amount,
            'wager' => $data[1]->wager,
            'cashable' => $data[1]->cashable,
            'duration' => $data[2]->duration,
            'duration_type' => $data[2]->type,
            'image' => $this->image,
            'strategy_id' => 1,
            'type_id' => 3,
        ];

        $validator = Validator::make($query, [
            'title' => 'required',
            'description' => 'required',
            'currency_id' => 'required',
        ]);

        if ($validator->passes()) {
            if ($image) {
                $path = $this->upload_image($image, $data[0]->title);
                $query['image'] = $path;
            }
            \App\Models\Bonuses::query()->insert($query);
            return new JsonResponse(['success' => 1, 'msg' => 'Success'], 200);
        }

        return response()->json(['errors' => $validator->errors()->all(), 'error_keys' => array_keys($validator->errors()->messages())]);
    }

    public function freespin($data, $currency)
    {
        $data = (array)$data;
        $data = array_column($data, 'model');
        $query = [
            'title' => $data[0]->title,
            'title_frontend' => $data[0]->title_frontend,
            'provider_id' => $data[0]->provider,
            'wager' => $data[0]->wager,
            'currency_id' => $currency,
            'status' => 1,
            'count' => $data[0]->count,
            'activate_duration' => $data[1]->activate_duration,
            'activate_duration_type' => $data[1]->type,
            'duration' => $data[2]->duration,
            'duration_type' => $data[2]->type,
        ];

        $id = \App\Models\FreespinBonusModel::query()->insertGetId($query);

        $query = [];

        foreach ((array)$data[0]->game as $game) {
            $query[] = [
                'fb_id' => $id,
                'game_id' => $game
            ];
        }

        FreespinBonusGamesModel::query()->insert($query);
        return new JsonResponse(['success' => 1, 'msg' => 'Success'], 200);
    }
    
    public function upload_image($file, $name): string
    {
        /** @var TYPE_NAME $exception */
        try {
            $slug = Carbon::now()->timestamp;
            $img = $file;
            $img_local_path = public_path('uploads/bonuses/' . $slug);
            $img_local_save_path = 'uploads/bonuses/' . $slug;
            Image::make($img)->encode('webp', 90)->save($img_local_path . '.webp');
            Image::make($img)->encode('png', 90)->save($img_local_path . '.png');
            return $img_local_save_path;
        }catch (NotReadableException $exception){
            return '';
        }
    }
}
