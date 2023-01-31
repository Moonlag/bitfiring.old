<?php
namespace App\Http\Traits;

use Exception;
use GuzzleHttp\Client;
use Illuminate\Validation\ValidationException;
use Psr\Http\Message\ResponseInterface;
use App\Http\Traits\DBTrait;
use App\Http\Traits\AuthTrait;

trait OGSTrait
{

	use DBTrait;
	use AuthTrait;
	
    private $uri_OGS = '';
    private $authToken_OGS = '';
    
    public function prepare_OGS(string $uri, string $authToken)
    {
		
        if (empty($uri)) {
            throw new Exception('The URI cannot be empty');
        }

        if (filter_var($uri, FILTER_VALIDATE_URL) === false) {
            throw new Exception('The URI is not valid');
        }

        $this->uri_OGS = $uri;
        $this->authToken_OGS = $authToken;
		
    }
	
    public function launch_game_OGS(array $params)
    {
        try {
			
            $res = $this->execute_OGS('openGame/v2', 'POST', $params);
            $data = $this->jsonResponse_OGS($res);
			 
            return $data;
			
        } catch (Exception $error) {
            throw $error;
        }
    }	

	
    private function getUri_OGS(): string
    {
        return $this->uri_OGS;
    }
	
    private function execute_OGS(string $endpoint, string $method, array $params): ResponseInterface
    {
        $client = new Client([
            'base_uri' => $this->getUri_OGS(),
        ]);
		
		$params = ["accessPassword" => $this->sig_hash_OGS($params)] + $params;

        switch ($method) {
            case 'GET':
                $data = ['query' => $params];
                break;
            case 'POST':
                $data = ['form_params' => $params];
                break;
            case 'PUT':
                $data = ['json' => $params];
        }

        return $client->request($method, $endpoint, $data);
    }

    public function sig_hash_OGS($params){
		
		$uri = "";
		foreach($params as $key=>$part) {
			if(strlen($part) > 0) {
				$uri .= "&".$key."=".$part;
			} else {
				$uri .= "&".$key;
			}
		}
		$uri = substr($uri, 1);		
		
		$to_md5 = $this->authToken_OGS.$uri;
		
        return strtoupper(md5($to_md5));
    }

    public function jsonResponse_OGS(object $json)
    {
        return json_decode($json->getBody()->getContents());
    }

    private function handlerError_OGS(object $response)
    {
        if (isset($response->ErrorCode) && isset($response->ErrorMessage) && !empty($response->ErrorCode) && !empty($response->ErrorMessage)) {
            throw new Exception("Oops, $response->ErrorMessage, $response->ErrorCode");
        }
    }

    private function customError_OGS(array $checkFields, object $response, string $code){
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

}
