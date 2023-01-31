<?php
namespace App\Http\Traits;

use GuzzleHttp\Client;
use Exception;
use Psr\Http\Message\ResponseInterface;

trait BoomingTrait
{
    private $uri;
    private $nonce;
    private $secret;
    private $apiKey;
    public $playUrl;
    private $sessionID;
    private $playerID;
    private $rounds;
    private $gameID;

    public function prepare_booming(string $uri, $nonce, string $secret, string $apiKey, $player_id=1)
    {
        if (empty($uri)) {
            throw new Exception('The URI cannot be empty');
        }

        if (filter_var($uri, FILTER_VALIDATE_URL) === false) {
            throw new Exception('The URI is not valid');
        }

        $this->uri = $uri;

        if (empty($nonce)) { 
            throw new Exception('The nonce cannot be empty');
        }
   
        $this->nonce = (int) $nonce;

        if (empty($secret)) {
            throw new Exception('The Secret Key cannot be empty');
        }

        $this->secret = $secret;

        if (empty($apiKey)) {
            throw new Exception('The API Key cannot be empty');
        }

        $this->apiKey = $apiKey;

        if (empty($player_id)) {
            throw new Exception('The API Key cannot be empty');
        }

        $this->playerID = $player_id;
    }


    public function booming_listOfGames(array $params)
    {
		
        $headers = $this->booming_setHeader($params, '/v2/games');
	
        try {
			
            $res = $this->booming_execute('/v2/games', 'GET', $params, $headers);
			
            $games = $this->booming_jsonResponse($res);

            $this->booming_handlerError($games);
            $this->booming_customError(['games'], $games, 3000);
			
            return $games->games;
			
        } catch (Exception $error) {
            throw $error;
        }
    }

    public function booming_sessions(array $params)
    {
				
        $headers = $this->booming_setHeader($params, '/v2/session', 'POST');

        try {
            $res = $this->booming_execute('/v2/session', 'POST', $params, $headers);
            $data = $this->booming_jsonResponse($res);
			
            //$this->booming_handlerError($session);
            //$this->customError(['session_id', 'play_url'], $session, 3000);
            //$this->playUrl = $session->play_url;
            //$this->booming_updateSessionID($session->session_id);
			
            return $data;
			
        } catch (Exception $error) {
            throw $error;
        }
    }
	
    public function booming_setHeader(array $params, $path, $method = 'GET', $BgApiKey = 'X-Bg-Api-Key', $BgNonce = 'X-Bg-Nonce', $BgSignature = 'X-Bg-Signature')
    {
        return [
            $BgApiKey => $this->booming_getApiKey(),
            $BgNonce => $this->nonce,
            $BgSignature => $this->booming_generatedUrl($path, $params, $method)
        ];
    }	

    private function booming_makeSignature(array $data, $method): string
    {
		
		if($method == 'GET') {
			return hash('SHA256', http_build_query($data));
		} else {
			return hash('SHA256', json_encode($data));
		}
    }

    private function booming_generatedUrl(string $path, array $data, $method): string
    {
        return hash_hmac('SHA512', $path . $this->nonce . $this->booming_makeSignature($data, $method), $this->booming_getSecretToken());
    }	

    private function booming_execute(string $endpoint, string $method, array $params, array $headers): ResponseInterface
    {
        $client = new Client([
            'base_uri' => $this->booming_getUri(),
            'headers' => $headers,
        ]);

        switch ($method) {
            case 'GET':
                $data = ['query' => $params];
                break;
            case 'POST':
                $data = ['json' => $params,];
                break;
            case 'PUT':
                $data = ['json' => $params];
        }

        return $client->request($method, $endpoint, $data);
    }
	
    private function booming_getUri(): string
    {
        return $this->uri;
    }

    private function booming_getSecretToken(): string
    {
        return $this->secret;
    }

    private function booming_getApiKey(): string
    {
        return $this->apiKey;
    }	
	

    public function booming_startingGame($method, $play_url): string
    {
        switch ($method) {
            case 'redirect':
                return redirect()->away($play_url);
            case 'iframe':
                return "<iframe src='$play_url'></iframe>";
            default:
                return $play_url;
        }
    }

    public function booming_getGameEvents(array $params): ResponseInterface
    {
        $headers = $headers = $this->booming_setHeader($params, 'X-Bg-Api-Key', 'Bg-Nonce', 'Bg-Signature');
        $playUrl = basename($this->playUrl);
        try {
            $res = $this->booming_execute('/v2/' . $playUrl, 'POST', $params, $headers);
            $evt = $this->booming_jsonResponse($res);
            $this->booming_handlerError($evt);
            if ($res->getStatusCode() === 200) {
                $this->customError(['balance'], $evt, 3003);
            }
            dd($evt);
        } catch (Exception $error) {
            throw $error;
        }
    }

    public function booming_terminatingSession(array $params): ResponseInterface
    {
        $headers = $this->booming_setHeader($params);
        try {
            $res = $this->booming_execute('/v2/sessions/' . $this->sessionID, 'PUT', $params, $headers);
            $terminating = $this->booming_jsonResponse($res);
            $this->booming_handlerError($terminating);
            return $res;
        } catch (Exception $error) {
            throw $error;
        }
    }

    public function booming_updatingBalance(array $params): ResponseInterface
    {
        $headers = $this->booming_setHeader($params);
        try {
            $res = $this->booming_execute('/v2/sessions/' . $this->sessionID, 'PUT', $params, $headers);
            $balance = $this->booming_jsonResponse($res);
            $this->booming_handlerError($balance);
            return $res;
        } catch (Exception $error) {
            throw $error;
        }
    }

    public function booming_sessionHistory(array $params): ResponseInterface
    {
        $headers = $this->booming_setHeader($params);
        try {
            $res = $this->booming_execute('/v2/sessions/' . $this->playerID, 'GET', $params, $headers);
            $history = $this->booming_jsonResponse($res);
            $this->booming_handlerError($history);
            $this->customError(['sessions'], $history, 3000);
            foreach ($history->sessions as $session) {
                $this->rounds = $session->rounds;
                break; //...
            }
            return $res;
        } catch (Exception $error) {
            throw $error;
        }
    }

    public function booming_roundsHistory(array $params): ResponseInterface
    {
        $headers = $this->booming_setHeader($params);
        try {
            $res = $this->booming_execute('/v2/sessions/' . $this->sessionID . '/rounds/' . implode($this->rounds), 'GET', $params, $headers);
            $rounds = $this->booming_jsonResponse($res);
            $this->booming_handlerError($rounds);
            $this->customError(['bet', 'win', 'symbols', 'lines', 'scatter', 'freespins'], $rounds, 3000);
            return $res;
        } catch (Exception $error) {
            throw $error;
        }
    }

    public function booming_gameDetails(array $params): ResponseInterface
    {
        $headers = $this->booming_setHeader($params);

        try {
            $res = $this->booming_execute('/v2/games/' . $this->gameID, 'GET', $params, $headers);
            $games = $this->booming_jsonResponse($res);
            $this->booming_handlerError($games);
            $this->customError(['sessions'], $games, 3000);
            return $res;
        } catch (Exception $error) {
            throw $error;
        }
    }
	

    private function booming_updateSessionID($session_id): string
    {
        return $this->sessionID = $session_id;
    }

    private function booming_handlerError(object $response)
    {
        if (isset($response->code) && isset($response->message)) {
            throw new Exception("Oops, $response->message, $response->code");
        }

        if (isset($response->error) && isset($response->message)) {
            throw new Exception("Oops, $response->error, $response->code");
        }
    }

    private function booming_customError(array $checkFields, object $response, string $code){
        $error = [];
        foreach ($checkFields as $field){
            if(!isset($response->$field)){
                array_push($error, $field);
            }
        }

        if(!empty($error)){
            throw new Exception("Oops, ".implode(", ", $error)." not found, error: $code");
        }
    }

    public function booming_jsonResponse(object $json)
    {
        $body = $json->getBody();
        $content = $body->getContents();
        return json_decode($content);
    }
}
