<?php
namespace App\Http\Traits;

use Exception;
use GuzzleHttp\Client;

trait SlottyTrait {
		
    private $uri;
    private $authToken;

    public function prepare_slotty(string $uri, string $authToken)
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

    public function getGameList_slotty(array $params): \Psr\Http\Message\ResponseInterface
    {
        return $this->execute_slotty('/integrations/bitfiring/rpc', 'GET', $params);
    }
	
    public function createSession_slotty(array $params): \Psr\Http\Message\ResponseInterface
    {
        return $this->execute_slotty('session_create', 'POST', $params);
    }
	
    private function execute_slotty(string $endpoint, string $method, array $params): \Psr\Http\Message\ResponseInterface
    {
		

        $client =  new Client([
            'base_uri' => $this->getUri_slotty(),
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
	
    private function getUri_slotty(): string
    {
        return $this->uri;
    }	
	
    public function demo_slotty(array $params): \Psr\Http\Message\ResponseInterface
    {
        return $this->execute_slotty('/demo', 'POST', $params);
    }	

    public function sessions_slotty(array $params): \Psr\Http\Message\ResponseInterface
    {
        return $this->execute_slotty('/sessions', 'POST', $params);
    }


    public function freespinsIssue_slotty(array $params): \Psr\Http\Message\ResponseInterface
    {
        return $this->execute_slotty('/freespins/issue', 'POST', $params);
    }
    
    public function freespinsCancel_slotty(array $params): \Psr\Http\Message\ResponseInterface
    {
        return $this->execute_slotty('/freespins/cancel', 'POST', $params);
    }
    
    public function freespinsStatus_slotty(array $params): \Psr\Http\Message\ResponseInterface
    {
        return $this->execute_slotty('/freespins/status', 'POST', $params);
    }
    
    public function roundDetails_slotty(array $params): \Psr\Http\Message\ResponseInterface
    {
        return $this->execute_slotty('/round/details', 'POST', $params);
    }
    
    public function userDetails_slotty(array $params): \Psr\Http\Message\ResponseInterface
    {
        return $this->execute_slotty('/user/details', 'POST', $params);
    }
    
    public function gameDetails_slotty(array $params): \Psr\Http\Message\ResponseInterface
    {
        return $this->execute_slotty('/game/details', 'POST', $params);
    }


    private function getAuthToken_slotty(): string
    {
        return $this->authToken;
    }

	
}