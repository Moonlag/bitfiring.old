<?php
namespace App\Http\Traits;

use Exception;
use GuzzleHttp\Client;

trait IgrosoftTrait {
		
    private $uri;
    private $authToken;

    public function prepare_igrosoft(string $uri, string $authToken)
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

    public function getGameList_igrosoft(array $params): \Psr\Http\Message\ResponseInterface
    {
        return $this->execute_igrosoft('games', 'POST', $params);
    }
	
    public function createSession_igrosoft(array $params): \Psr\Http\Message\ResponseInterface
    {
        return $this->execute_igrosoft('session_create', 'POST', $params);
    }
	
    private function execute_igrosoft(string $endpoint, string $method, array $params): \Psr\Http\Message\ResponseInterface
    {
		

        $client =  new Client([
            'base_uri' => $this->getUri_igrosoft(),
            'headers' => [
				'X-Casino-Merchant-Id' 	  	  => $params['merchant_id'],
				'X-Casino-Transaction-Id' 	  => $params['transaction_id'],
				'X-Casino-Timestamp' 	  	  => $params['timestamp'],
				'X-Casino-Always-HttpSuccess' => false,
				'X-Casino-Signature' 	  	  => $this->makeSignature_igrosoft($params),
				'Accept'				  	  => "application/json",
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

    private function makeSignature_igrosoft(array $params): string
    {
		
		$to_md5 = $params['merchant_id'].$params['transaction_id'].$params['timestamp']."3CCFE6B21B21D0886DA705BC26D47EE9";	
		return md5($to_md5);
		
    }	
	
    private function getUri_igrosoft(): string
    {
        return $this->uri;
    }	
	
    public function demo_igrosoft(array $params): \Psr\Http\Message\ResponseInterface
    {
        return $this->execute_igrosoft('/demo', 'POST', $params);
    }	

    public function sessions_igrosoft(array $params): \Psr\Http\Message\ResponseInterface
    {
        return $this->execute_igrosoft('/sessions', 'POST', $params);
    }


    public function freespinsIssue_igrosoft(array $params): \Psr\Http\Message\ResponseInterface
    {
        return $this->execute_igrosoft('/freespins/issue', 'POST', $params);
    }
    
    public function freespinsCancel_igrosoft(array $params): \Psr\Http\Message\ResponseInterface
    {
        return $this->execute_igrosoft('/freespins/cancel', 'POST', $params);
    }
    
    public function freespinsStatus_igrosoft(array $params): \Psr\Http\Message\ResponseInterface
    {
        return $this->execute_igrosoft('/freespins/status', 'POST', $params);
    }
    
    public function roundDetails_igrosoft(array $params): \Psr\Http\Message\ResponseInterface
    {
        return $this->execute_igrosoft('/round/details', 'POST', $params);
    }
    
    public function userDetails_igrosoft(array $params): \Psr\Http\Message\ResponseInterface
    {
        return $this->execute_igrosoft('/user/details', 'POST', $params);
    }
    
    public function gameDetails_igrosoft(array $params): \Psr\Http\Message\ResponseInterface
    {
        return $this->execute_igrosoft('/game/details', 'POST', $params);
    }


    private function getAuthToken_igrosoft(): string
    {
        return $this->authToken;
    }

	
}