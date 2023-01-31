<?php

namespace App\Orchid\Screens\Factory;

use App\Models\Bonuses;
use App\Models\BonusIssue;
use App\Models\Currency;
use App\Models\Payments;
use App\Models\Players;
use App\Models\Transactions;
use App\Models\Wallets;
use App\Models\WalletsTemp;
use App\Orchid\Layouts\User\UserEditLayout;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Facades\DB;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Screen;
use Orchid\Support\Color;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class UserScreen extends Screen
{
    /**
     * Display header name.
     *
     * @var string
     */
    public $name = 'UserScreen';

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
            Layout::block(Layout::rows([
                Button::make('Create Player')
                    ->class('btn btn-secondary')
                    ->icon('plus')
                    ->method('wagering_test')
            ]))
                ->title(__('Wagering Test'))
                ->description(__("Создать пользователя с рандомным количеством депозитов и 99% Wagering "))
            ,
            Layout::block(Layout::rows([
                Button::make('Create Player')
                    ->class('btn btn-secondary')
                    ->icon('plus')
                    ->method('bonus_locked')
            ]))
                ->title(__('Bonus Locked Test'))
                ->description(__("Создать пользователя с рандомным количеством депозитов и 99% Bonus Locked"))
            ,
            Layout::block(Layout::rows([
                Button::make('Create Player')
                    ->class('btn btn-secondary')
                    ->icon('plus')
                    ->method('balance_wallet')
            ]))
                ->title(__('Balance Test'))
                ->description(__("Создать пользователя со всеми кошельками и Balance 50 USD")),

        ];
    }

    public function set_primary($player){
        $wallet = $player->wallets->first();
        if(isset($wallet->id)){
            $wallet->primary = 1;
            $wallet->save();
        }
    }

    public function wagering_test()
    {
        $player = Players::factory()->create();
        $currency = Currency::all()->whereIn('id', [7, 9, 12, 14]);
        $currency = $currency->random(rand(2, 4));

        foreach ($currency as $value) {
            $this->create_wallets($player, $value);
        }

        foreach ($player->wallets as $wallet){
            $amount = rand(25, 500);
            $count = 2;
            for($i = 1; $i <= $count; $i++){
                $bonus = Bonuses::get()->random();
                $bonus = $this->create_deposits($player, $wallet, $amount, $bonus);
                if($i <= $count - 1){
                    $bonus->wagered = $bonus->to_wager * 0.99;
                    $bonus->save();
                }
            }
        }


        $this->set_primary($player);
        Toast::success('Success');
    }

    public function bonus_locked()
    {
        $player = Players::factory()->create();
        $currency = Currency::all()->whereIn('id', [7, 9, 12, 14]);
        $currency = $currency->random(rand(2, 4));

        foreach ($currency as $value) {
            $this->create_wallets($player, $value);
        }

        foreach ($player->wallets as $wallet){
            $amount = rand(25, 500);
            $count = 2;
            for($i = 1; $i <= $count; $i++){
                $bonus = Bonuses::get()->random();
                $bonus = $this->create_deposits($player, $wallet, $amount, $bonus);
                if($i <= $count - 1){
                    $bonus->fixed_amount = $bonus->locked_amount * 0.99;
                    $bonus->save();
                }
            }
        }

        $this->set_primary($player);
        Toast::success('Success');
    }

    public function balance_wallet()
    {
        $player = Players::factory()->create();
        $currency = Currency::all()->whereIn('id', [7, 9, 12, 14]);
        $amount = 50;

        foreach ($currency as $value) {
            $this->create_wallets($player, $value);
        }

        $player = Players::find($player->id);

        foreach ($player->wallets as $wallet){
            $this->create_deposits($player, $wallet, $amount);
        }

        $this->set_primary($player);
        Toast::success('Success');
    }

    public function create_wallets($user, $currency)
    {
        Wallets::factory()->state([
            'user_id' => $user->id,
            'currency_id' => $currency->id,
        ])->create();
    }

    public function create_bonuses($user, $wallet, $bonus, $payment){
        $percentage = ((int)$bonus->amount / 100);

        $bonus_amount = $payment->amount / $wallet->currency->rate;
        $bonus_amount = $bonus->max >= ($bonus_amount * $percentage) ? ($bonus_amount * $percentage) : $bonus->max;

        DB::table('bonuses_user')->insert([
            'user_id' => $user->id,
            'published' => 1,
            'stage' => 2,
            'currency' => $wallet->currency->id,
            'bonus_id' => $bonus->id,
            'amount' => $bonus->amount,
            'wager' => $bonus->wager,
            'created_at' => \Illuminate\Support\Carbon::now(),
        ]);


        $locked_amount = ((float)$payment->amount / $wallet->currency->rate) + $bonus_amount;
        $to_wager = $locked_amount * $bonus->wager;

       $bonus = BonusIssue::create([
            'user_id' => $user->id,
            'currency_id' => $wallet->currency->id,
            'bonus_id' => $bonus->id,
            'amount' => $bonus_amount,
            'locked_amount' => $locked_amount,
            'reference_id' => $payment->id,
            'to_wager' => $to_wager,
            'created_at' => \Illuminate\Support\Carbon::now(),
            'stage' => 2,
            'status' => 2,
        ]);

        $wallet->balance = new Expression('balance + ' . $bonus_amount * $wallet->currency->rate);
        $wallet->save();

        return $bonus;
    }

    public function create_deposits($user, $wallet, $amount, $bonus = false){
        $payment = Payments::create([
            'email' => $user->email,
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'amount' => $wallet->currency->rate * $amount,
            'type_id' => 3,
            'status' => 1,
            'source_address' => WalletsTemp::get()->random()->wallet,
            'payment_system_id' => $wallet->currency->payment_system->id,
            'created_at' => Carbon::now(),
            'currency_id' => $wallet->currency->id,
            'player_action' => 1,
            'network_fee' => 0.79,
        ]);


        Transactions::create([
            'amount' => $wallet->currency->rate * $amount,
            'bonus_part' => 0,
            'currency_id' => $wallet->currency->id,
            'reference_id' => $payment->id,
            'wallet_id' => $wallet->id,
            'player_id' => $user->id,
            'type_id' => 1,
            'reference_type_id' => 5,
        ]);

        $wallet->balance = $wallet->currency->rate * $amount;
        $wallet->save();

        if($bonus){
            return $this->create_bonuses($user, $wallet, $bonus, $payment);
        }
    }
}
