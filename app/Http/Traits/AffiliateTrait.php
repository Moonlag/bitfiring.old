<?php


namespace App\Http\Traits;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Http;
use Psr\Http\Message\ResponseInterface;

trait AffiliateTrait
{
    private $url;
    private $data;

    public function prepare_callback($url, $data){
        $this->url = $url;
        $this->data = $data;
    }

    public function open_callback(){
        $client = new Client([
            'base_uri' => $this->url,
        ]);

        $client->request('POST', '/register_open', $this->data);
    }

    public function deposit_callback(){
        $client = new Client([
            'base_uri' => $this->url,
        ]);

        $client->request('POST', '/register_open', $this->data);
    }

    public function affiliate_deposit($aid, $player_id, $amount, $deposit_id, $bonus = null)
    {
        Http::affiliates()
            ->post('/register_deposit', [
                'aid' => $aid,
                'player_id' => $player_id,
                'amount' => $amount,
                'amount_cents' => ($amount * 100),
                'deposit_id' => $deposit_id,
                'bonus' => $bonus
            ]);
    }
}
