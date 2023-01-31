<?php

namespace App\Orchid\Screens\Partners;

use App\Models\Countries;
use App\Models\Currency;
use App\Traits\AuthTrait;
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

class NewPartner extends Screen
{
    use AuthTrait;
    /**
     * Display header name.
     *
     * @var string
     */
    public $name = 'New Partner';

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

                Password::make('password')
                    ->required(true)
                    ->title('Password'),

                Input::make('company')
                    ->type('text')
                    ->required(true)
                    ->title('Company'),

                Input::make('firstname')
                    ->type('text')
                    ->required(true)
                    ->title('Firstname'),

                Input::make('lastname')
                    ->type('text')
                    ->required(true)
                    ->title('Lastname'),

                Input::make('nickname')
                    ->type('text')
                    ->required(true)
                    ->title('Nickname'),

                Input::make('address')
                    ->type('text')
                    ->required(true)
                    ->title('Company address'),

                Input::make('phone')
                    ->type('text')
                    ->required(true)
                    ->title('Phone'),

                Select::make('country_id')
                    ->empty('No select', '0')
                    ->fromModel(Countries::class, 'name')
                    ->required(true)
                    ->title('Country'),
            ])
        ];
    }

    public function new_player(Request $request)
    {

        $validator = Validator::make($request->all(),
            [
                'email' => 'required|email|unique:partners',
                'firstname' => 'required|string',
                'lastname' => 'required|string',
                'company' => 'required|string',
                'password' => 'required',
                'nickname' => 'required|string',
                'country_id' => 'required',
                'phone' => 'required',
                'address' => 'required',
            ]);

        if ($validator->fails()) {
            Toast::warning(__('Opss, invalid request'));
        } else {
            $commission_id = $this->get_default_commission();

            $partner = \App\Models\Partner::create([
                'email' => $request->email,
                'firstname' => $request->firstname,
                'lastname' => $request->lastname,
                'company' => $request->company,
                'nickname' => $request->nickname,
                'countries_id' => $request->country_id,
                'phone' => $request->phone,
                'address' => $request->address,
                'password' => Hash::make($request->password),
                'commission_id' => $commission_id
            ]);

            $this->create_default_company($partner);

            Toast::success(__('New partner, Success'));
        }
    }
}
