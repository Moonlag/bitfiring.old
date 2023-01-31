<?php

namespace App\Orchid\Screens\Players;

use App\Models\AllowedIp;
use App\Models\Countries;
use App\Traits\ChangesTrait;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Fields\DateTimer;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\Switcher;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Toast;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Mail;

class EditPlayer extends Screen
{
    use ChangesTrait;
    /**
     * Display header name.
     *
     * @var string
     */
    public $name = 'EditPlayer';

    public $status;
    /**
     * Display header description.
     *
     * @var string|null
     */
    public $description = 'EditPlayer';


    public $player_id;

    /**
     * Query data.
     *
     * @return array
     */
    public function query(\App\Models\Players $model, Request $request): array
    {
        $this->exist = $model->exists;
        if ($this->exist) {
            $this->name = $model->email;
            $this->status = (int)$request->status ?? 0;
            $this->description = 'id: ' . $model->id;
            $this->player_id = $model->id;
        }

        return [
            'player' => $model
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
            Button::make('Save')
                ->class('btn btn-rounded btn-secondary')
                ->icon('check')
                ->parameters([
                    'id' => $this->player_id
                ])
                ->method('update_player'),
            Link::make('Return')
                ->class('btn btn-rounded btn-outline-secondary')
                ->icon('left')
                ->route($this->status ? 'platform.players' : 'platform.players.profile', ['id' => $this->player_id]),
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
            Layout::rows([
                Input::make('player.firstname')
                    ->type('text')
                    ->required(true)
                    ->title('Firstname'),

                Input::make('player.lastname')
                    ->type('text')
                    ->required(true)
                    ->title('Lastname'),

                Input::make('player.email')
                    ->type('text')
                    ->required(true)
                    ->title('Email'),

                Select::make('player.gender')
                    ->empty('No select', '0')
                    ->title('Gender')
                    ->options([
                        1 => 'Male',
                        2 => 'Female'
                    ]),

                DateTimer::make('player.dob')
                    ->title('Dob')
                    ->format('Y-m-d'),

                Select::make('player.country_id')
                    ->empty('No select', '0')
                    ->fromModel(Countries::class, 'name')
                    ->title('Country'),

                Input::make('player.city')
                    ->type('text')
                    ->title('City'),

                Input::make('player.address')
                    ->type('text')
                    ->title('Address'),

                Select::make('allowed_ips')
                    ->multiple()
                    ->taggable()
                    ->fromQuery(AllowedIp::query()->where('user_id', $this->player_id)->select('ip', 'id'), 'ip')
                    ->value(array_column(AllowedIp::query()->where('user_id', $this->player_id)->select('id')->get()->toArray(), 'id'))
                    ->title('Allowed IPs'),

                Switcher::make('player.promo_sms')
                    ->sendTrueOrFalse()
                    ->title('Promo sms'),

                Switcher::make('player.promo_email')
                    ->title('Promo email')
                    ->sendTrueOrFalse()
                    ->vertical(),
            ])
        ];
    }

    public function update_player(Request $request, \App\Models\Players $model)
    {
        try {
            $player = $request->player;
            $player['fullname'] = $player['firstname'] . ' ' . $player['lastname'];


            $validator = Validator::make(['email' => $player['email']], [
                'email' => Rule::unique('players')->ignore($model),
            ]);

            if ($validator->passes()) {
                \App\Models\Players::query()->where('id', $request->id)->update($player);

                $this->set_changes($player, $model);

                Toast::info('You have successfully.');

                $url = $request->url();

                Mail::send([],[], function ($message) use ($url){
                    $message->to('magistriam@gmail.com');
                    $message->subject('Changed detected!');
                    $message->setBody("Changed detected!
Changed to player details detected $url", 'text/plain');
                });
            }else {
                Toast::error('Email is taken');
            }


        } catch (ValidationException $e) {
            $msg = '';
            foreach ($e->errors() as $key => $value) {
                $msg .= join('', $value);
            }
            Toast::warning($msg);
        }
    }

    public function set_changes($request, \App\Models\Players $model){
        $changes = [];
        foreach (array_keys($request) as $key){
            if($request[$key] != $model->$key){
                $changes[] = [
                    'request_name' => $key,
                    'request' => json_encode([$request[$key]]),
                    'user_id' => $model->id,
                ];
            }
        }
        if(!empty($changes)){
            $this->prepare($model->id);
            $this->insert_changes($changes);
        }
    }
}
