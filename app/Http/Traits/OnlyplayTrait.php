<?php
namespace App\Http\Traits;

use Exception;
use GuzzleHttp\Client;

trait OnlyplayTrait {
		
    private $uri;
    private $authToken;

    public function prepare_onlyplay(string $uri, string $authToken)
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

    public function real_onlyplay(array $params): \Psr\Http\Message\ResponseInterface
    {
		$params = $this->getAuthToken_onlyplay($params);
		
        return $this->execute_onlyplay('/api/get_frame', 'POST', $params);
    }
	
    public function getGameList_onlyplay(array $params): \Psr\Http\Message\ResponseInterface
    {
		
        return $this->execute_onlyplay('', 'GET', $params);
		
    }

    private function getAuthToken_onlyplay($params): array
    {
        
		ksort($params);
		$string = "";
		
		foreach($params as $key=>$value) {
			$string .= $key.$value;
		}
		$string .= $this->authToken;
		
		$params['sign'] = sha1($string);
		
		return $params;
		
    }	
	
    private function execute_onlyplay(string $endpoint, string $method, array $params): \Psr\Http\Message\ResponseInterface
    {
		

        $client =  new Client([
            'base_uri' => $this->getUri_onlyplay(),
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
	
    private function getUri_onlyplay(): string
    {
        return $this->uri;
    }	


	
}