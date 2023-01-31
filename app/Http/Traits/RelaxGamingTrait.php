<?php
namespace App\Http\Traits;

use Exception;
use GuzzleHttp\Client;
use Illuminate\Validation\ValidationException;
use Psr\Http\Message\ResponseInterface;

trait RelaxGamingTrait
{

    private $uri;
    private $username;
    private $password; 
    private $customerid;

    public function prepare_relax(string $uri, string $username, string $password)
    {
        if (empty($uri)) {
            throw new Exception('The URI cannot be empty');
        }

        if (filter_var($uri, FILTER_VALIDATE_URL) === false) {
            throw new Exception('The URI is not valid');
        }

        $this->uri = $uri;

        if (empty($username) || empty($password)) {
            throw new Exception('The username or password cannot be empty');
        }
		
        $this->username = $username;
        $this->password = $password;

    }

    public function relax_get_available_games(array $params)
    {
        try {
            $res = $this->relax_execute('/papi/1.0/casino/games/getgames', 'GET', $params);
            $sample = $this->relax_jsonResponse($res);

            $this->relax_handlerError($sample);
			
			return $sample;
			
            try {
                $this->validate($sample, [
                    'status' => ['required', 'string'],
                    'cdnbaseurl' => ['required', 'string'],
                    'games' => ['required', 'array'],
                    'games.*.gameid' => ['required', 'integer'],
                    'games.*.name' => ['required', 'integer'],
                    'games.*.studio' => ['required', 'integer'],
                    'games.*.channels' => ['required', 'array'],
                    'games.*.resolutions' => ['required', 'object'],
                    'games.*.resolutions.*.web' => ['sometimes', 'required', 'array'],
                    'games.*.freespins' => ['required', 'object'],
                    'games.*.freespins.*.channels' => ['required', 'array'],
                    'games.*.freespins.*.paylines' => ['required', 'array'],
                    'games.*.freespins.*.legalbetsizes' => ['required', 'array'],
                    'games.*.launcherurl' => ['sometimes', 'required', 'string'],
                    'games.*.altlauncherurl' => ['sometimes', 'required', 'string'],
                    'games.*.altcdnbaseurl' => ['sometimes', 'required', 'string'],
                ]);
            } catch (ValidationException $error){
                throw $error->errors();
            }
   
        } catch (Exception $error) {
            throw $error;
        }
    }	
	
    private function relax_execute(string $endpoint, string $method, array $params): ResponseInterface
    {
		
        $client = new Client([		
			'auth' => [$this->username, $this->password],
            'base_uri' => $this->getUri(),
            'headers' => ['Content-Type' => 'application/json'],
        ]);
		
        switch ($method) {
            case 'GET':
                $data = [
                    'query' => $params,
                ];
                break;
            case 'POST':
                $data = [
                    'json' => $params,
                ];
                break;
            case 'PUT':
                $data = [
                        'json' => $params
                    ];
        }

        return $client->request($method, $endpoint, $data);
    }
	


    private function relax_getUri(): string
    {
        return $this->uri;
    }
	
	
	
    public function relax_add_freespins(array $params): ResponseInterface
    {
        try {
            $res = $this->execute('/papi/1.0/casino/freespins/add', 'POST', $params);
            $sample = $this->jsonResponse($res);
            $this->handlerError($sample);
            try {
                $this->validate($sample, [
                    'status' => ['required', 'string'],
                    'txid' => ['required', 'string'],
                    'freespinids' => ['required', 'array'],
                ]);
            }catch (ValidationException $error){
                throw $error->errors();
            }
            dd($sample);
        } catch (Exception $error) {
            throw $error;
        }
    }

    public function relax_query_possible_counts(array $params): ResponseInterface
    {
        try {
            $res = $this->execute('/papi/1.0/casino/freespins/querypossiblecounts', 'GET', $params);
            $sample = $this->jsonResponse($res);
            $this->handlerError($sample);
            try {
                $this->validate($sample, [
                    'currency' => ['required', 'string'],
                    'g' => ['required', 'string'],
                    'l' => ['required', 'integer'],
                    'possibleCounts' => ['required', 'array'],
                    'status' => ['required', 'string'],
                    'totalvalue' => ['required', 'integer'],
                ]);
            }catch (ValidationException $error){
                throw $error->errors();
            }
//            $this->customError([], $sample, 3001);
            dd($sample);
        } catch (Exception $error) {
            throw $error;
        }
    }

    public function relax_get_free_spins(array $params): ResponseInterface
    {
        try {
            $res = $this->execute('/papi/1.0/casino/freespins/' . $this->customerid, 'GET', $params);
            $sample = $this->jsonResponse($res);
            $this->handlerError($sample);
            try {
                $this->validate($sample, [
                    'status' => ['required', 'string'],
                    'freespins' => ['required', 'array'],
                    'freespins.*.expire' => ['required', 'integer'],
                    'freespins.*.freespinvalue' => ['required', 'integer'],
                    'freespins.*.currency' => ['required', 'string'],
                    'freespins.*.gameid' => ['required', 'string'],
                    'freespins.*.paylines' => ['required', 'integer'],
                    'freespins.*.spinamount' => ['required', 'integer'],
                    'freespins.*.ctime' => ['required', 'integer'],
                    'freespins.*.promocode' => ['required', 'string'],
                ]);
            }catch (ValidationException $error){
                throw $error->errors();
            }
            dd($sample);
        } catch (Exception $error) {
            throw $error;
        }
    }

    public function relax_cancel_free_spins(array $params): ResponseInterface
    {
        try {
            $res = $this->execute('/papi/1.0/casino/freespins/cancel', 'POST', $params);
            $sample = $this->jsonResponse($res);
            $this->handlerError($sample);
            try {
                $this->validate($sample, [
                    'expire' => ['required', 'string'],
                    'freespinsid' => ['required', 'integer'],
                ]);
            }catch (ValidationException $error){
                throw $error->errors();
            }
            dd($sample);
        } catch (Exception $error) {
            throw $error;
        }
    }

    public function relax_add_feature_triggers(array $params): ResponseInterface
    {
        try {
            $res = $this->execute('/papi/1.0/casino/featuretriggers/add', 'POST', $params);
            $sample = $this->jsonResponse($res);
            $this->handlerError($sample);
            try {
                $this->validate($sample, [
                    'status' => ['required', 'string'],
                    'txid' => ['required', 'string'],
                ]);
            }catch (ValidationException $error){
                throw $error->errors();
            }
            dd($sample);
        } catch (Exception $error) {
            throw $error;
        }
    }

    public function relax_get_feature_triggers(array $params): ResponseInterface
    {
        try {
            $res = $this->execute('/papi/1.0/casino/featuretriggers/' . $this->customerid, 'GET', $params);
            $sample = $this->jsonResponse($res);
            $this->handlerError($sample);
            try {
                $this->validate($sample, [
                    'status' => ['required', 'string'],
                    'featuretriggers' => ['required', 'array'],
                    'featuretriggers.*.expire' => ['required', 'integer'],
                    'featuretriggers.*.freespinvalue' => ['required', 'integer'],
                    'featuretriggers.*.currency' => ['required', 'string'],
                    'featuretriggers.*.gameid' => ['required', 'string'],
                    'featuretriggers.*.paylines' => ['required', 'integer'],
                    'featuretriggers.*.spinamount' => ['required', 'integer'],
                    'featuretriggers.*.ctime' => ['required', 'integer'],
                    'featuretriggers.*.promocode' => ['required', 'string'],
                ]);
            }catch (ValidationException $error){
                throw $error->errors();
            }
            dd($sample);
        } catch (Exception $error) {
            throw $error;
        }
    }

    public function relax_add_tokens(array $params): ResponseInterface
    {
        try {
            $res = $this->execute('/papi/1.0/casino/tokens/add', 'POST', $params);
            $sample = $this->jsonResponse($res);
            $this->handlerError($sample);
            try {
                $this->validate($sample, [
                    'status' => ['required', 'string'],
                    'txid' => ['required', 'string'],
                ]);
            }catch (ValidationException $error){
                throw $error->errors();
            }
            dd($sample);
        } catch (Exception $error) {
            throw $error;
        }
    }

    public function relax_remove_tokens(array $params): ResponseInterface
    {
        try {
            $res = $this->execute('/papi/1.0/casino/tokens/remove', 'POST', $params);
            $sample = $this->jsonResponse($res);
            $this->handlerError($sample);
            try {
                $this->validate($sample, [
                    'status' => ['required', 'string'],
                    'txid' => ['required', 'string'],
                ]);
            }catch (ValidationException $error){
                throw $error->errors();
            }
            dd($sample);
        } catch (Exception $error) {
            throw $error;
        }
    }


    public function relax_get_round_state(array $params): ResponseInterface
    {
        try {
            $res = $this->execute('/papi/1.0/casino/games/getroundstate', 'GET', $params);
            $sample = $this->jsonResponse($res);
            $this->handlerError($sample);
            try {
                $this->validate($sample, [
                    'roundid' => ['required', 'integer'],
                    'gameref' => ['required', 'string'],
                    'state' => ['required', 'string'],
                    'winamount' => ['required', 'integer'],
                ]);
            }catch (ValidationException $error){
                throw $error->errors();
            }
            dd($sample);
        } catch (Exception $error) {
            throw $error;
        }
    }

    public function relax_unfinished_rounds(array $params): ResponseInterface
    {
        try {
            $res = $this->execute('/papi/1.0/casino/rounds/unfinished', 'GET', $params);
            $sample = $this->jsonResponse($res);
            $this->handlerError($sample);
            try {
                $this->validate($sample, [
                    'betamount' => ['required', 'integer'],
                    'channel' => ['required', 'string'],
                    'createtime' => ['required', 'string'],
                    'gameref' => ['required', 'string'],
                    'remoteusername' => ['required', 'string'],
                    'roundid' => ['required', 'integer'],
                ]);
            }catch (ValidationException $error){
                throw $error->errors();
            }
            dd($sample);
        } catch (Exception $error) {
            throw $error;
        }
    }

    public function relax_jackpot_values(array $params): ResponseInterface
    {
        try {
            $res = $this->execute('/papi/1.0/casino/jackpots/values', 'GET', $params);
            $sample = $this->jsonResponse($res);
            $this->handlerError($sample);
            try {
                $this->validate($sample, [
                    'jackpotvalues' => ['required', 'array'],
                    'jackpotvalues.*.amount' => ['required', 'string'],
                    'jackpotvalues.*.games' => ['required', 'array'],
                    'jackpotvalues.*.id' => ['required', 'integer'],
                    'jackpotvalues.*.name' => ['required', 'string'],
                    'status' => ['sometimes', 'required', 'string'],
                ]);
            }catch (ValidationException $error){
                throw $error->errors();
            }
            dd($sample);
        } catch (Exception $error) {
            throw $error;
        }
    }

    public function relax_get_replay(array $params): ResponseInterface
    {
        try {
            $res = $this->execute('/papi/1.0/casino/replay/get', 'GET', $params);
            $sample = $this->jsonResponse($res);
            $this->handlerError($sample);
            try {
                $this->validate($sample, [
                    'replay' => ['required', 'string'],
                    'status' => ['required', 'string'],
                ]);
            }catch (ValidationException $error){
                throw $error->errors();
            }
            dd($sample);
        } catch (Exception $error) {
            throw $error;
        }
    }

    public function relax_get_game_hashes(array $params): ResponseInterface
    {
        try {
            $res = $this->execute('/papi/1.0/casino/datafeed/gamehashes', 'GET', $params);
            $sample = $this->jsonResponse($res);
            $this->handlerError($sample);
            try {
                $this->validate($sample, [
                    'hashes' => ['required', 'array'],
                    'hashes.*.file' => ['required', 'string'],
                ]);
            }catch (ValidationException $error){
                throw $error->errors();
            }
            dd($sample);
        } catch (Exception $error) {
            throw $error;
        }
    }

    public function relax_emulate_auto_resolve(array $params): ResponseInterface
    {
        try {
            $res = $this->execute('/papi/1.0/casino/testing/emulateautoresolve', 'GET', $params);
            $sample = $this->jsonResponse($res);
            $this->handlerError($sample);
            try {
                $this->validate($sample, [
                    'status' => ['required', 'string'],
                ]);
            }catch (ValidationException $error){
                throw $error->errors();
            }
            dd($sample);
        } catch (Exception $error) {
            throw $error;
        }
    }

    public function relax_version(array $params): ResponseInterface
    {
        try {
            $res = $this->execute('/papi/1.0/version', 'GET', $params);
            $sample = $this->jsonResponse($res);
            $this->handlerError($sample);
            try {
                $this->validate($sample, [
                    'status' => ['required', 'string'],
                    'version' => ['required', 'string'],
                ]);
            }catch (ValidationException $error){
                throw $error->errors();
            }
            dd($sample);
        } catch (Exception $error) {
            throw $error;
        }
    }
    public function relax_jsonResponse(object $json)
    {
        $content = $json->getBody()->getContents();
        return json_decode($content);
    }

    private function relax_handlerError(object $response)
    {
        if (isset($response->status) && $response->status === 'error') {
            throw new Exception("Oops, Invalid parameters, code: " . $response->errorcode, 403);
        }
        if (isset($response->errorcode)) {
            throw new Exception("Oops, Invalid AUTHORIZED code: " . $response->errorcode, 401);
        }
    }

}
