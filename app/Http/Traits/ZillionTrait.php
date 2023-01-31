<?php
namespace App\Http\Traits;

use Exception;
use GuzzleHttp\Client;

trait ZillionTrait {
		
    private $uri_zillion;
    private $authToken_zillion;

    public function prepare_zillion(string $uri, string $authToken)
    {
        if (empty($uri)) {
            throw new Exception('The URI cannot be empty');
        }

        if (filter_var($uri, FILTER_VALIDATE_URL) === false) {
            throw new Exception('The URI is not valid');
        }

        $this->uri_zillion = $uri;

        if (empty($authToken)) {
            throw new Exception('The AUTH_TOKEN cannot be empty');
        }

        $this->authToken_zillion = $authToken;
    }

    private function execute_zillion(string $endpoint, string $method, array $params): \Psr\Http\Message\ResponseInterface
    {

        $client =  new Client([
            'base_uri' => $this->getUri_zillion(),
            'headers' => [
                'x-request-sign' => $this->makeSignature_zillion($params, $method)
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

    private function getUri_zillion(): string
    {
        return $this->uri_zillion;
    }

    private function getAuthToken_zillion(): string
    {
        return $this->authToken_zillion;
    }

    private function makeSignature_zillion(array $data, $method): string
    {
		if ($method == 'POST')
			return hash_hmac('sha256', json_encode($data), $this->getAuthToken_zillion());
		else
			return hash_hmac('sha256', http_build_query($data), $this->getAuthToken_zillion());
    }
		
	
    public function demo_zillion(array $params): \Psr\Http\Message\ResponseInterface
    {
        return $this->execute_zillion('demo', 'POST', $params);
    }	

    public function sessions_zillion(array $params): \Psr\Http\Message\ResponseInterface
    {
        return $this->execute_zillion('sessions', 'POST', $params);
    }

    public function getGameList_zillion(array $params): \Psr\Http\Message\ResponseInterface
    {
        return $this->execute_zillion('/api/games', 'GET', $params);
    }

    public function freespinsIssue_zillion(array $params): \Psr\Http\Message\ResponseInterface
    {
        return $this->execute_zillion('/freespins/issue', 'POST', $params);
    }
    
    public function freespinsCancel_zillion(array $params): \Psr\Http\Message\ResponseInterface
    {
        return $this->execute_zillion('/freespins/cancel', 'POST', $params);
    }
    
    public function freespinsStatus_zillion(array $params): \Psr\Http\Message\ResponseInterface
    {
        return $this->execute_zillion('/freespins/status', 'POST', $params);
    }
    
    public function roundDetails_zillion(array $params): \Psr\Http\Message\ResponseInterface
    {
        return $this->execute_zillion('/round/details', 'POST', $params);
    }
    
    public function userDetails_zillion(array $params): \Psr\Http\Message\ResponseInterface
    {
        return $this->execute_zillion('/user/details', 'POST', $params);
    }
    
    public function gameDetails_zillion(array $params): \Psr\Http\Message\ResponseInterface
    {
        return $this->execute_zillion('/game/details', 'POST', $params);
    }

}