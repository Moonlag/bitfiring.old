<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class VerifyPlayer extends Mailable
{
    use Queueable, SerializesModels;
    public $player;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($player)
    {
        $this->player = $player;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $url = url(config('app.url'));

        return $this->subject('Welcome to BitFiring')->view('emails.verify_email', ['url' => $url, 'email' => $this->player->email,  'token' => $this->player->verifyPlayer->token]);
    }
}
