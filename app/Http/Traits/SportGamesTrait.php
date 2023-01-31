<?php
namespace App\Http\Traits;

use Exception;
use GuzzleHttp\Client;

trait SportGamesTrait {
		
    private $uri;
    private $authToken;

    public function prepare_sportgames(string $uri, string $authToken)
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

    public function sportgames_getGameList(array $params)
    {
        return $this->sportgames_execute('gameList?hash='.$this->sportgames_makeSignature($params, 'POST'), 'POST', $params);
    }

    public function sportgames_freespinsIssue(array $params)
    {
        return $this->sportgames_execute('/freespins/issue', 'POST', $params);
    }
    
    public function sportgames_freespinsCancel(array $params)
    {
        return $this->sportgames_execute('/freespins/cancel', 'POST', $params);
    }
    
    public function sportgames_freespinsStatus(array $params)
    {
        return $this->sportgames_execute('/freespins', 'POST', $params);
    }
    
    public function sportgames_roundDetails(array $params)
    {
        return $this->sportgames_execute('/round/details', 'POST', $params);
    }
    
    public function sportgames_userDetails(array $params)
    {
        return $this->sportgames_execute('/user/details', 'POST', $params);
    }
    
    public function sportgames_gameDetails(array $params)
    {
        return $this->sportgames_execute('/game/details', 'POST', $params);
    }

    private function sportgames_execute(string $endpoint, string $method, array $params)
    {
        $client =  new Client([
            'base_uri' => $this->sportgames_getUri(),
            'headers' => [
                'x-request-sign' => $this->sportgames_makeSignature($params, $method)
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

    private function sportgames_getUri()
    {
        return $this->uri;
    }

    private function sportgames_getAuthToken()
    {
        return $this->authToken;
    }

    private function sportgames_makeSignature(array $data, $method)
    {
		if ($method == 'POST')
			return hash_hmac('sha256', json_encode($data), $this->sportgames_getAuthToken());
		else
			return hash_hmac('sha256', http_build_query($data), $this->sportgames_getAuthToken());
    }
	
}