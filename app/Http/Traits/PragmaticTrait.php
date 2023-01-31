<?php
namespace App\Http\Traits;

use Exception;
use GuzzleHttp\Client;

trait PragmaticTrait {
		
    private $uri;
    private $authToken;

    public function prepare_pragmaticplay(string $uri, string $authToken)
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

    public function getGameList_pragmaticplay(array $params): \Psr\Http\Message\ResponseInterface
    {
		
		$result = $this->hash_it($params);
		$params['hash'] = $result[0];
		
        return $this->execute_pragmaticplay('getCasinoGames/?'.$result[1], 'POST', $params);
    }
	
    public function createSession_pragmaticplay(array $params): \Psr\Http\Message\ResponseInterface
    {
        return $this->execute_pragmaticplay('session_create', 'POST', $params);
    }
	
    public function hash_it($params) {
		
		$uri = "";
		foreach($params as $key=>$part) {
			if(strlen($part) > 0) {
				$uri .= "&".$key."=".$part;
			} else {
				$uri .= "&".$key;
			}
		}		
		$uri = substr($uri, 1);
		
		$to_md5 = $uri.$this->authToken;
		
        return [md5($to_md5), $uri."&hash=".md5($to_md5)];
    }	
	
    private function execute_pragmaticplay(string $endpoint, string $method, array $params): \Psr\Http\Message\ResponseInterface
    {
		
        $client =  new Client([
            'base_uri' => $this->getUri_pragmaticplay(),
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
	
    private function getUri_pragmaticplay(): string
    {
        return $this->uri;
    }	
	
    public function demo_pragmaticplay(array $params): \Psr\Http\Message\ResponseInterface
    {
        return $this->execute_pragmaticplay('/demo', 'POST', $params);
    }	

    public function sessions_pragmaticplay(array $params): \Psr\Http\Message\ResponseInterface
    {
        return $this->execute_pragmaticplay('/sessions', 'POST', $params);
    }


    public function freespinsIssue_pragmaticplay(array $params): \Psr\Http\Message\ResponseInterface
    {
        return $this->execute_pragmaticplay('/freespins/issue', 'POST', $params);
    }
    
    public function freespinsCancel_pragmaticplay(array $params): \Psr\Http\Message\ResponseInterface
    {
        return $this->execute_pragmaticplay('/freespins/cancel', 'POST', $params);
    }
    
    public function freespinsStatus_pragmaticplay(array $params): \Psr\Http\Message\ResponseInterface
    {
        return $this->execute_pragmaticplay('/freespins/status', 'POST', $params);
    }
    
    public function roundDetails_pragmaticplay(array $params): \Psr\Http\Message\ResponseInterface
    {
        return $this->execute_pragmaticplay('/round/details', 'POST', $params);
    }
    
    public function userDetails_pragmaticplay(array $params): \Psr\Http\Message\ResponseInterface
    {
        return $this->execute_pragmaticplay('/user/details', 'POST', $params);
    }
    
    public function gameDetails_pragmaticplay(array $params): \Psr\Http\Message\ResponseInterface
    {
        return $this->execute_pragmaticplay('/game/details', 'POST', $params);
    }


    private function getAuthToken_pragmaticplay(): string
    {
        return $this->authToken;
    }

	
}