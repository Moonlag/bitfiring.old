<?php
namespace App\Http\Traits;

use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;
use Exception;
use Illuminate\Database\Query\Expression;

trait EvoplayTrait
{
    private $uri;
    private $projectID;
    private $version;
    private $secretKey;

    public function prepare_evoplay(string $uri, string $projectID, string $version, string $secretKey)
    {
        if (empty($uri)) {
            throw new Exception('The URI cannot be empty');
        }

        if (filter_var($uri, FILTER_VALIDATE_URL) === false) {
            throw new Exception('The URI is not valid');
        }

        $this->uri = $uri;

        if (empty($projectID)) {
            throw new Exception('The project id cannot be empty');
        }

        $this->projectID = $projectID;

        if (empty($version)) {
            throw new Exception('The version cannot be empty');
        }

        $this->version = $version;

        if (empty($secretKey)) {
            throw new Exception('The secret key cannot be empty');
        }

        $this->secretKey = $secretKey;
    }
	
    public function evo_getListGames(array $params)
    {
        try {
			
            $res = $this->evo_execute('/Game/getList', 'GET', $params);		
            $data = $this->evo_jsonResponse($res);
            $this->evo_handlerError($data);
            $this->evo_customError([], $data, 4000);
			
            return $data;
			
        } catch (Exception $error) {
            throw $error;
        }
    }

    public function evo_getUrlGames(array $params)
    {
        try {
			
            $res = $this->evo_execute('/Game/getURL', 'GET', $params);
            $sample = $this->evo_jsonResponse($res);
            $this->evo_handlerError($sample);
            $this->evo_customError([], $sample, 4000);
			
            return $sample;
			
        } catch (Exception $error) {
            throw $error;
        }
    }	

    private function evo_execute(string $endpoint, string $method, array $params)
    {
        $client = new Client([
            'base_uri' => $this->evo_getUri(),
        ]);


        switch ($method) {
            case 'GET':
                $data = ['query' => $this->evo_signatureFormed($params),
                ];
                break;
            case 'POST':
                $data = ['json' => $params];
                break;
            case 'PUT':
                $data = ['json' => $params];
        }

        return $client->request($method, $endpoint, $data);
    }	
	

    private function evo_getUri(): string
    {
        return $this->uri;
    }

    private function evo_signatureFormed($params)
    {
        $signature['project'] = $this->projectID;
        $signature['version'] = $this->version;
        $signature['signature'] = $this->evo_getSignature($this->projectID, $this->version, $params, $this->secretKey);

        if (!empty($params)) {
            $signature = array_merge($signature, $params);
        }
        return $signature;
    }
	

    /**
     * $project_id - your project system ID (number)
     * $version - callback or API version
     * $args - array with API method or callback parameters. API parameters list you can find in API method description
     * $secret_key - your system key
     */
    private function evo_getSignature(string $project_id, string $version, array $args, string $secret_key)
    {
        $md5 = array();
        $md5[] = $project_id;
        $md5[] = $version;
        foreach ($args as $required_arg) {
            $arg = $required_arg;
            if (is_array($arg)) {
                if (count($arg)) {
                    $recursive_arg = '';
                    array_walk_recursive($arg, function ($item) use (& $recursive_arg) {
                        if (!is_array($item)) {
                            $recursive_arg .= ($item . ':');
                        }
                    });
                    $md5[] = substr($recursive_arg, 0, strlen($recursive_arg) - 1);
                } else {
                    $md5[] = '';
                }
            } else {
                $md5[] = $arg;
            }
        };
        $md5[] = $secret_key;
        $md5_str = implode('*', $md5);
        $md5 = md5($md5_str);;
        return $md5;
    }	


    public function evo_getPayoutGames(array $params): ResponseInterface
    {
        try {
            $res = $this->execute('/Game/getAvailablePayouts', 'GET', $params);
            $sample = $this->jsonResponse($res);
            $this->handlerError($sample);
            $this->customError([], $sample, 4000);
            dd($sample);
        } catch (Exception $error) {
            throw $error;
        }
    }

    public function evo_getTypeGames(array $params): ResponseInterface
    {
        try {
            $res = $this->execute('/Game/getTypes', 'GET', $params);
            $sample = $this->jsonResponse($res);
            $this->handlerError($sample);
            $this->customError([], $sample, 4000);
            dd($sample);
        } catch (Exception $error) {
            throw $error;
        }
    }

    public function evo_getRegisterBonus(array $params): ResponseInterface
    {
        try {
            $res = $this->execute('/Game/registerBonus', 'GET', $params);
            $sample = $this->jsonResponse($res);
            $this->handlerError($sample);
            $this->customError([], $sample, 4000);
            dd($sample);
        } catch (Exception $error) {
            throw $error;
        }
    }

    public function evo_getRemoveBonus(array $params): ResponseInterface
    {
        try {
            $res = $this->execute('/Game/removeBonus', 'GET', $params);
            $sample = $this->jsonResponse($res);
            $this->handlerError($sample);
            $this->customError([], $sample, 4000);
            dd($sample);
        } catch (Exception $error) {
            throw $error;
        }
    }

    public function evo_getEventGames(array $params): ResponseInterface
    {
        try {
            $res = $this->execute('/Game/getEvent', 'GET', $params);
            $sample = $this->jsonResponse($res);
            $this->handlerError($sample);
            $this->customError([], $sample, 4000);
            dd($sample);
        } catch (Exception $error) {
            throw $error;
        }
    }

    public function evo_getRoundGames(array $params): ResponseInterface
    {
        try {
            $res = $this->execute('/Game/getRound', 'GET', $params);
            $sample = $this->jsonResponse($res);
            $this->handlerError($sample);
            $this->customError([], $sample, 4000);
            dd($sample);
        } catch (Exception $error) {
            throw $error;
        }

    }

    public function evo_getGameInfo(array $params): ResponseInterface
    {
        try {
            $res = $this->execute('/Game/getGameInfo', 'GET', $params);
            $sample = $this->jsonResponse($res);
            $this->handlerError($sample);
            $this->customError([], $sample, 4000);
            dd($sample);
        } catch (Exception $error) {
            throw $error;
        }
    }

    public function evo_getAvailableLanguages(array $params): ResponseInterface
    {
        try {
            $res = $this->execute('/Game/getAvailableLanguages', 'GET', $params);
            $sample = $this->jsonResponse($res);
            $this->handlerError($sample);
            $this->customError([], $sample, 4000);
            dd($sample);
        } catch (Exception $error) {
            throw $error;
        }
    }

    public function evo_getAvailableBets(array $params): ResponseInterface
    {
        try {
            $res = $this->execute('/Game/getAvailableBets', 'GET', $params);
            $sample = $this->jsonResponse($res);
            $this->handlerError($sample);
            $this->customError([], $sample, 4000);
            dd($sample);
        } catch (Exception $error) {
            throw $error;
        }
    }

    public function evo_getAvailableBetsInMoney(array $params): ResponseInterface
    {
        try {
            $res = $this->execute('/Game/getAvailableBetsInMoney', 'GET', $params);
            $sample = $this->jsonResponse($res);
            $this->handlerError($sample);
            $this->customError([], $sample, 4000);
            dd($sample);
        } catch (Exception $error) {
            throw $error;
        }
    }


    private function evo_handlerError(object $response)
    {
        if (isset($response->status) && $response->status === 'error') {
            if (isset($response->error->info->param)) {
                throw new Exception("Oops, " . $response->error->message . ' (' . $response->error->info->param . "), code: " . $response->error->code);
            } else {
                throw new Exception("Oops, " . $response->error->message . ", code: " . $response->error->code);
            }

        }
    }

    private function evo_customError(array $checkFields, object $response, string $code)
    {
        $error = [];
        foreach ($checkFields as $field) {
            if (!isset($response->$field)) {
                array_push($error, $field);
            }
        }

        if (!empty($error)) {
            throw new Exception("Oops, " . implode(", ", $error) . " not found, error: $code");
        }
    }


    public function evo_jsonResponse(object $json)
    {
        $body = $json->getBody();
        $content = $body->getContents();
        return json_decode($content);
    }
}
