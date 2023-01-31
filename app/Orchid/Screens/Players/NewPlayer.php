<?php

namespace App\Orchid\Screens\Players;

use App\Models\AllowedIp;
use App\Models\Countries;
use App\Models\Currency;
use App\Models\Limits;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Fields\DateTimer;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Password;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\Switcher;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class NewPlayer extends Screen
{
    /**
     * Display header name.
     *
     * @var string
     */
    public $name = 'New Player';

//    /**
//     * Display header description.
//     *
//     * @var string|null
//     */
//    public $description = 'NewPlayer';

    /**
     * Query data.
     *
     * @return array
     */
    public function query(): array
    {
        return [];
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
                ->icon('check')
                ->class('btn btn-secondary')
                ->method('new_player'),
            Link::make('Return')
                ->class('btn btn-outline-secondary mb-2')
                ->icon('left')
                ->route('platform.players'),
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
                Input::make('email')
                    ->type('email')
                    ->required(true)
                    ->title('Email'),

                Input::make('firstname')
                    ->type('text')
                    ->required(true)
                    ->title('Firstname'),

                Input::make('lastname')
                    ->type('text')
                    ->required(true)
                    ->title('Lastname'),

                Password::make('password')
                    ->required(true)
                    ->title('Password'),

                Select::make('allowed_ips')
                    ->multiple()
                    ->taggable()
                    ->title('Allowed IPs'),

                Select::make('country_id')
                    ->empty('No select', '0')
                    ->fromModel(Countries::class, 'name')
                    ->required(true)
                    ->title('Country'),

                Select::make('currency_id')
                    ->empty('No select', '0')
                    ->fromQuery(Currency::query()->where('parent_id', '=' ,0), 'name')
                    ->required(true)
                    ->title('Currency'),

                Switcher::make('promo_sms')
                    ->title('Promo sms'),

                Switcher::make('promo_email')
                    ->title('Promo email')
                    ->sendTrueOrFalse()->vertical(),

                Select::make('self_exclusion')
                    ->options([
                        0 => 'Empty',
                        4 => '1 month',
                        5 => '3 months',
                        6 => '6 months',
                        7 => '24 hours',
                        8 => '7 days',
                        9 => '14 days',
                        13 => '9 months',
                        14 => '1 year',
                        15 => '3 years',
                        16 => 'forever',
                    ])
                    ->title('Self exclusion'),

                Select::make('disable_reason')
                    ->options([
                            0 => 'Empty',
                            1 => 'Antifraud lock',
                            2 => 'Auth Duplicate',
                            3 => 'Chargeback',
                            4 => 'License Rules',
                            5 => 'Manual',
                            6 => 'Negative Balance',
                            7 => 'Personal ID Duplicate',
                            8 => 'Phone Number Duplicate',
                            9 => 'Registration Duplicate',
                        ]
                    )
                    ->title('Disable reason'),
            ])
        ];
    }

    public function new_player(Request $request)
    {

        $validator = Validator::make($request->all(),
            [
                'email' => 'required|email',
                'firstname' => 'required|string',
                'lastname' => 'required|string',
                'country_id' => 'required|string',
                'currency_id' => 'required|string',
                'password' => 'required',
            ]);

        if ($validator->fails()) {
            Toast::warning(__('Opss, invalid request'));
        } else {
            $id = \App\Models\Players::query()->insertGetId([
                'email' => $request->email,
                'firstname' => $request->firstname,
                'lastname' => $request->lastname,
                'fullname' => $request->firstname . ' ' . $request->lastname,
                'country_id' => $request->country_id,
                'currency_id' => $request->currency_id,
                'password' => Hash::make($request->password),
                'promo_sms' => $request->promo_sms,
                'promo_email' => $request->promo_email,
            ]);
            if (!empty($request->allowed_ips)) {
                AllowedIp::query()->insert(array_map(function ($ip) use ($id) {
                    return ['ip' => $ip, 'user_id' => $id];
                }, $request->allowed_ips));
            }
            if (!empty($request->self_exclusion)) {
                Limits::query()->insert([
                    'type_id' => 2,
                    'user_id' => $id,
                    'period_id' => $request->self_exclusion,
                    'staff_id' => $request->user()->id
                ]);
            }
            Toast::success(__('New player, Success'));
        }
    }
}
