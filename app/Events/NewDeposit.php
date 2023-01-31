<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class NewDeposit implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $user;
    public $address;
    public $amount;
    public $payment_id;
    public $wallet_id;
    public $payment_system_id;
    public $address_id;
    public $bonus;
    public $currency_id;
    public $aid;
    public $usd;
    public $freespin;
    public $bonus_option_id;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($user, $address, $amount, $payment_id, $wallet_id, $payment_system_id, $address_id, $bonus, $currency_id, $aid, $usd, $freespin, $bonus_option_id)
    {
        $this->user = $user;
        $this->address = $address;
        $this->amount = $amount;
        $this->payment_id = $payment_id;
        $this->wallet_id = $wallet_id;
        $this->payment_system_id = $payment_system_id;
        $this->address_id = $address_id;
        $this->bonus = $bonus;
        $this->currency_id = $currency_id;
        $this->aid = $aid;
        $this->usd = $usd;
        $this->freespin = $freespin;
        $this->bonus_option_id = $bonus_option_id;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel("channel-deposit");
    }
}
