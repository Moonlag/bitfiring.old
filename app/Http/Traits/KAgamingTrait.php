<?php
namespace App\Http\Traits;

use Exception;
use GuzzleHttp\Client;

trait KAgamingTrait {
		
    private $uri;
    private $authToken;

    public function prepare_kagaming(string $uri, string $authToken)
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

    public function kagaming_getGameList(array $params)
    {
        return $this->kagaming_execute('gameList?hash='.$this->kagaming_makeSignature($params, 'POST'), 'POST', $params);
    }

    public function kagaming_freespinsIssue(array $params)
    {
        return $this->kagaming_execute('/freespins/issue', 'POST', $params);
    }
    
    public function kagaming_freespinsCancel(array $params)
    {
        return $this->kagaming_execute('/freespins/cancel', 'POST', $params);
    }
    
    public function kagaming_freespinsStatus(array $params)
    {
        return $this->kagaming_execute('/freespins', 'POST', $params);
    }
    
    public function kagaming_roundDetails(array $params)
    {
        return $this->kagaming_execute('/round/details', 'POST', $params);
    }
    
    public function kagaming_userDetails(array $params)
    {
        return $this->kagaming_execute('/user/details', 'POST', $params);
    }
    
    public function kagaming_gameDetails(array $params)
    {
        return $this->kagaming_execute('/game/details', 'POST', $params);
    }

    private function kagaming_execute(string $endpoint, string $method, array $params)
    {
        $client =  new Client([
            'base_uri' => $this->kagaming_getUri(),
            'headers' => [
                'x-request-sign' => $this->kagaming_makeSignature($params, $method)
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

    private function kagaming_getUri()
    {
        return $this->uri;
    }

    private function kagaming_getAuthToken()
    {
        return $this->authToken;
    }

    private function kagaming_makeSignature(array $data, $method)
    {
		if ($method == 'POST')
			return hash_hmac('sha256', json_encode($data), $this->kagaming_getAuthToken());
		else
			return hash_hmac('sha256', http_build_query($data), $this->kagaming_getAuthToken());
    }
	
}