<?php
namespace App\Http\Traits;

use Exception;
use GuzzleHttp\Client;

trait BgamingTrait {
		
    private $uri;
    private $authToken;

    public function prepare_bgaming(string $uri, string $authToken)
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

    public function bgaming_demo(array $params): \Psr\Http\Message\ResponseInterface
    {
        return $this->bgaming_execute('demo', 'POST', $params);
    }	

    public function bgaming_sessions(array $params): \Psr\Http\Message\ResponseInterface
    {
        return $this->bgaming_execute('sessions', 'POST', $params);
    }

    public function bgaming_getGameList(array $params)
    {
        return $this->bgaming_execute('/gamelist', 'GET', $params);
    }

    public function bgaming_freespinsIssue(array $params)
    {
        return $this->bgaming_execute('freespins/issue', 'POST', $params);
    }
    
    public function bgaming_freespinsCancel(array $params)
    {
        return $this->bgaming_execute('/freespins/cancel', 'POST', $params);
    }
    
    public function bgaming_freespinsStatus(array $params)
    {
        return $this->bgaming_execute('/freespins', 'POST', $params);
    }
    
    public function bgaming_roundDetails(array $params)
    {
        return $this->bgaming_execute('/round/details', 'POST', $params);
    }
    
    public function bgaming_userDetails(array $params)
    {
        return $this->bgaming_execute('/user/details', 'POST', $params);
    }
    
    public function bgaming_gameDetails(array $params)
    {
        return $this->bgaming_execute('/game/details', 'POST', $params);
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