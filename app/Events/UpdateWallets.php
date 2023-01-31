<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UpdateWallets implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $user;
    public $wallet_id;
    public $amount;
    public $bet;
    public $currency_id;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($user, $id, $amount, $bet, $currency_id = 0)
    {
        $this->user = $user;
        $this->wallet_id = $id;
        $this->amount = $amount;
        $this->bet = $bet;
        $this->currency_id = $currency_id;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel("channel-wallet");
    }
}
