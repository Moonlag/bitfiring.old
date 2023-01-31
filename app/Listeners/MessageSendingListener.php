<?php

namespace App\Listeners;

use Illuminate\Mail\Events\MessageSending;
use Symfony\Component\Mime\Crypto\SMimeSigner;
use Symfony\Component\Mime\Email;

class MessageSendingListener
{
    public function handle(MessageSending $event)
    {
        $signer = new SMimeSigner('./../../../../conf/web/bitfiring.com/ssl/bitfiring.com.crt', './../../../../../conf/web/bitfiring.com/ssl/bitfiring.com.key');
        dd($event->message->getSwiftMessage());
        $signedMessage = $signer->sign($event->message->getSwiftMessage());

        $event->message->setHeaders($signedMessage->getHeaders());
        $event->message->setBody($signedMessage->getBody());
    }
}
