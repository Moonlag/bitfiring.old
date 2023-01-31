<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class Main extends Mailable
{
    use Queueable, SerializesModels;

    public $body;
    public $subject;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($body, $subject)
    {
        $this->body = $body;
        $this->subject = $subject;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {

        return $this->subject($this->subject)->view('emails.main');
    }
}
