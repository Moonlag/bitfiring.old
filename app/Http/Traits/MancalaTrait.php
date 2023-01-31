<?php

namespace App\Http\Traits;

use Exception;
use GuzzleHttp\Client;
use Illuminate\Support\Arr;

trait MancalaTrait
{

    private $uri;
    private $clientGuid;
    private $apiKey;

    public function prepare_mancala(string $uri, string $clientGuid, string $apiKey)
    {
        if (empty($uri)) {
            throw new Exception('The URI cannot be empty');
        }

        if (filter_var($uri, FILTER_VALIDATE_URL) === false) {
            throw new Exception('The URI is not valid');
        }

        $this->uri = $uri;

        if (empty($clientGuid)) {
            throw new Exception('The CLIENT_GUID cannot be empty');
        }

        $this->clientGuid = $clientGuid;

        if (empty($apiKey)) {
            throw new Exception('The API_KEY cannot be empty');
        }

        $this->apiKey = $apiKey;
    }

    public function mancala_getToken(array $params): \Psr\Http\Message\ResponseInterface
    {
        return $this->mancala_execute('GetToken/', 'POST', $params);
    }

    public function mancala_getAvailableGames(array $params): \Psr\Http\Message\ResponseInterface
    {
        return $this->mancala_execute('GetAvailableGames/', 'POST', $params);
    }

    public function mancala_GetAvailableBets(array $params): \Psr\Http\Message\ResponseInterface
    {
        return $this->mancala_execute('GetAvailableBets/', 'POST', $params);
    }

    public function mancala_GetJackpotInfo(array $params)
    {
        return $this->mancala_execute('GetJackpotInfo/', 'GET', $params);
    }

    public function mancala_GetGamesPopularity(array $params)
    {
        return $this->mancala_execute('GetGamesPopularity/', 'POST', $params);
    }

    public function mancala_freespins(array $params)
    {
        return $this->mancala_execute('AddFreeSpinRules/', 'POST', $params);
    }

    private function mancala_execute(string $endpoint, string $method, array $params, array $hash_key = [])
    {
        $client = new Client([
            'base_uri' => $this->mancala_getUri(),

        ]);

        $params['hash'] = $this->mancala_makeHash($method, $params, $hash_key);

        switch ($method) {
            case 'GET':
                $data = ['query' => $params];
                break;
            case 'POST':
                $data = ['json' => $params];
        }

        return $client->request($method, $endpoint, $data);
    }

    private function mancala_getUri()
    {
        return $this->uri;
    }

    private function mancala_getСlientGuid()
    {
        return $this->clientGuid;
    }

    private function mancala_getApiKey()
    {
        return $this->apiKey;
    }

    private function mancala_makeHash($method, $params, $hash_key)
    {
        return md5($method . $this->mancala_getСlientGuid() . Arr::join(Arr::only($params, $hash_key), ''));
    }

}
