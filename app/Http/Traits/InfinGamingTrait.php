<?php
namespace App\Http\Traits;

use Exception;
use GuzzleHttp\Client;
use Illuminate\Validation\ValidationException;
use Psr\Http\Message\ResponseInterface;
use App\Http\Traits\DBTrait;
use App\Http\Traits\AuthTrait;

trait InfinGamingTrait
{

	use DBTrait;
	use AuthTrait;
	
    private $uri_InfinGaming = '';
    private $authToken_InfinGaming = '';
    
    public function prepare_InfinGaming(string $uri, string $authToken)
    {
		
        if (empty($uri)) {
            throw new Exception('The URI cannot be empty');
        }

        if (filter_var($uri, FILTER_VALIDATE_URL) === false) {
            throw new Exception('The URI is not valid');
        }

        $this->uri_InfinGaming = $uri;
        $this->authToken_InfinGaming = $authToken;
		
    }
	
    public function getgamelist_InfinGaming(array $params)
    {
        try {
			
            $res = $this->execute_InfinGaming('', 'GET', $params);
			
            $data = $this->xmlResponse_InfinGaming($res);
			
            return $data;
			
        } catch (Exception $error) {
            throw $error;
        }
    }
	
    public function launch_game_InfinGaming(array $params)
    {
        try {
			
            $res = $this->execute_InfinGaming('openGame/v2', 'POST', $params);
            $data = $this->xmlResponse_InfinGaming($res);
			 
            return $data;
			
        } catch (Exception $error) {
            throw $error;
        }
    }	

	
    private function getUri_InfinGaming(): string
    {
        return $this->uri_InfinGaming;
    }
	
    private function execute_InfinGaming(string $endpoint, string $method, array $params): ResponseInterface
    {
        $client = new Client([
            'base_uri' => $this->getUri_InfinGaming(),
        ]);
		
		//$params = ["accessPassword" => $this->sig_hash_InfinGaming($params)] + $params;

        switch ($method) {
            case 'GET':
                $data = ['query' => $params];
                break;
            case 'POST':
                $data = ['form_params' => $params];
                break;
            case 'PUT':
                $data = ['xml' => $params];
        }

        return $client->request($method, $endpoint, $data);
    }

    public function sig_hash_InfinGaming($params){
		
		$uri = "";
		foreach($params as $key=>$part) {
			if(strlen($part) > 0) {
				$uri .= "&".$key."=".$part;
			} else {
				$uri .= "&".$key;
			}
		}
		$uri = substr($uri, 1);		
		
		$to_md5 = $this->authToken_InfinGaming.$uri;
		
        return strtoupper(md5($to_md5));
    }

    public function xmlResponse_InfinGaming(object $xml)
    {		
        return $xml->getBody()->getContents();
    }

    private function handlerError_InfinGaming(object $response)
    {
        if (isset($response->ErrorCode) && isset($response->ErrorMessage) && !empty($response->ErrorCode) && !empty($response->ErrorMessage)) {
            throw new Exception("Oops, $response->ErrorMessage, $response->ErrorCode");
        }
    }

    private function customError_InfinGaming(array $checkFields, object $response, string $code){
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
