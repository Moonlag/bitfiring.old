<?php
namespace App\Http\Traits;

use Exception;
use GuzzleHttp\Client;

trait BelatraTrait {
		
    private $uri;
    private $authToken;

    public function prepare_belatra(string $uri, string $authToken)
    {
        if (empty($uri)) {
            throw new Exception('The URI cannot be empty');
        }

        if (filter_var($uri, FILTER_VALIDATE_URL) === false) {
            throw new Exception('The URI is not valid');
        }

        $this->uri = $uri;

        if (empty($authToken)) {
            throw new Exception('The AUTH_TOKEN cannot be empty');
        }

        $this->authToken = $authToken;
    }

    public function demo(array $params): \Psr\Http\Message\ResponseInterface
    {
        return $this->execute('/demo', 'POST', $params);
    }	

    public function sessions(array $params): \Psr\Http\Message\ResponseInterface
    {
        return $this->execute('/sessions', 'POST', $params);
    }

    public function getGameList(array $params): \Psr\Http\Message\ResponseInterface
    {
        return $this->execute('/gamelist', 'GET', $params);
    }

    public function freespinsIssue(array $params): \Psr\Http\Message\ResponseInterface
    {
        return $this->execute('/freespins/issue', 'POST', $params);
    }
    
    public function freespinsCancel(array $params): \Psr\Http\Message\ResponseInterface
    {
        return $this->execute('/freespins/cancel', 'POST', $params);
    }
    
    public function freespinsStatus(array $params): \Psr\Http\Message\ResponseInterface
    {
        return $this->execute('/freespins/status', 'POST', $params);
    }
    
    public function roundDetails(array $params): \Psr\Http\Message\ResponseInterface
    {
        return $this->execute('/round/details', 'POST', $params);
    }
    
    public function userDetails(array $params): \Psr\Http\Message\ResponseInterface
    {
        return $this->execute('/user/details', 'POST', $params);
    }
    
    public function gameDetails(array $params): \Psr\Http\Message\ResponseInterface
    {
        return $this->execute('/game/details', 'POST', $params);
    }

    private function execute(string $endpoint, string $method, array $params): \Psr\Http\Message\ResponseInterface
    {
        $client =  new Client([
            'base_uri' => $this->getUri(),
            'headers' => [
                'x-request-sign' => $this->makeSignature($params, $method)
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

    private function getUri(): string
    {
        return $this->uri;
    }

    private function getAuthToken(): string
    {
        return $this->authToken;
    }

    private function makeSignature(array $data, $method): string
    {
		if ($method == 'POST')
			return hash_hmac('sha256', json_encode($data), $this->getAuthToken());
		else
			return hash_hmac('sha256', http_build_query($data), $this->getAuthToken());
    }
	
}