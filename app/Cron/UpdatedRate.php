<?php


namespace App\Cron;

use App\Models\Currency;
use Carbon\Carbon;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Psr\Http\Message\ResponseInterface;

class UpdatedRate
{
    private $uri = 'https://min-api.cryptocompare.com';
    private $api_key = 'b75c66d60679be2e19906acfddd926de002e21b26e29c026600f823b68544429';

    public function __invoke()
    {
        $this->get_currency_rate();
    }

    public function get_currency_rate()
    {
        try {
            $res = $this->execute('/data/pricemulti', 'GET', $this->get_params());
            $this->updated_currency_rate($this->jsonResponse($res));
        } catch (Exception $error) {
            throw $error;
        }
    }

    public function updated_currency_rate($input){
        if($input['USD']){
            $now = Carbon::now();
            foreach ($input['USD'] as $key => $value){
                DB::table('currency')->where('code', $key)->update(['rate' => $value, 'updated_at' => $now]);
            }
            echo 'New Rate:'. json_encode($input);
        }
    }

    public function get_params()
    {
        $fsyms = DB::table('payment_system')->where('active', 1)->select('name')->get();

        return [
            'fsyms' => 'USD',
            'tsyms' => join(',', array_column($fsyms->toArray(), 'name')),
            'api_key' => $this->api_key
        ];

    }

    private function execute(string $endpoint, string $method, array $params): ResponseInterface
    {

        $client = new Client([
            'base_uri' => $this->getUri(),
            'headers' => ['Content-Type' => 'application/json'],
        ]);

        switch ($method) {
            case 'GET':
                $data = [
                    'query' => $params,
                ];
                break;
            case 'POST':
                $data = [
                    'json' => $params,
                ];
                break;
            case 'PUT':
                $data = [
                    'json' => $params
                ];
        }

        return $client->request($method, $endpoint, $data);
    }

    public function jsonResponse(object $json)
    {
        $content = $json->getBody()->getContents();

        $content = json_decode($content, true);

        if (json_last_error() != JSON_ERROR_NONE) {
            die('json_decode error: '.json_last_error_msg());
        };

        return $content;
    }


    private function getUri(): string
    {
        return $this->uri;
    }
}
