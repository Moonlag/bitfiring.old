<?php
namespace App\Http\Traits;

use Exception;
use GuzzleHttp\Client;

trait BgaminFreespinTrait {

    private $uri = 'https://bgaming-network.com/a8r/bitfiring/';
    private $authToken;

    public function prepare_bgaming(string $authToken)
    {
        if (empty($authToken)) {
            throw new Exception('The AUTH_TOKEN cannot be empty');
        }

        $this->authToken = $authToken;
    }

    public function bgaming_freespinsCancel(array $params)
    {
        return $this->bgaming_execute('/freespins/cancel', 'POST', $params);
    }

    public function bgaming_freespinsStatus(array $params)
    {
        return $this->bgaming_execute('/freespins', 'POST', $params);
    }


    public function bgaming_freespinsIssue(array $params)
    {
        return $this->bgaming_execute('freespins/issue', 'POST', $params);
    }

    private function bgaming_execute(string $endpoint, string $method, array $params)
    {
        $client =  new Client([
            'base_uri' => $this->bgaming_getUri(),
            'headers' => [
                'x-request-sign' => $this->bgaming_makeSignature($params, $method)
            ]
        ]);

        switch ($method) {
            case 'GET':
                $data = ['query' => $params];
                break;
            case 'POST':
                $data = ['json' => $params];
        }

        return $client->request($method, $endpoint, $data);
    }

    private function bgaming_getUri()
    {
        return $this->uri;
    }

    private function bgaming_getAuthToken()
    {
        return $this->authToken;
    }

    private function bgaming_makeSignature(array $data, $method)
    {
        if ($method == 'POST')
            return hash_hmac('sha256', json_encode($data), $this->bgaming_getAuthToken());
        else
            return hash_hmac('sha256', http_build_query($data), $this->bgaming_getAuthToken());
    }

}