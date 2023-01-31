<?php
namespace App\Http\Traits;

use Exception;
use GuzzleHttp\Client;
use Illuminate\Validation\ValidationException;
use Psr\Http\Message\ResponseInterface;
use App\Http\Traits\DBTrait;
use App\Http\Traits\AuthTrait;

trait SpinomenalTrait
{

	use DBTrait;
	use AuthTrait;
	
    public $uri_spinomenal = '';
    
    public function prepare_spinomenal(string $uri)
    {
		
        if (empty($uri)) {
            throw new Exception('The URI cannot be empty');
        }

        if (filter_var($uri, FILTER_VALIDATE_URL) === false) {
            throw new Exception('The URI is not valid');
        }

        $this->uri_spinomenal = $uri;
    }
	
    public function get_games_spinomenal(array $params)
    {
        try {
			
            $res = $this->execute_spinomenal('/reporting/getgames', 'GET', $params);

            $games = $this->jsonResponse_spinomenal($res);
			
            return $games;
			
        } catch (Exception $error) {
            throw $error;
        }
    }
	
    public function launch_demo_game_spinomenal(array $params)
    {
        try {
			
            $res = $this->execute_spinomenal('/GameLauncher/LaunchDemoGame', 'POST', $params);
            $data = $this->jsonResponse_spinomenal($res);
			
            return $data;
			
        } catch (Exception $error) {
            throw $error;
        }
    }	

    public function generate_token_spinomenal(array $params)
    {
        try {
			
            $res = $this->execute_spinomenal('GameLauncher/GenerateToken', 'POST', $params);
            $data = $this->jsonResponse_spinomenal($res);
			
            return $data;
			
        } catch (Exception $error) {
            throw $error;
        }
    }	
	
    private function getUri_spinomenal(): string
    {
        return $this->uri_spinomenal;
    }
	
    private function execute_spinomenal(string $endpoint, string $method, array $params): ResponseInterface
    {
        $client = new Client([
            'base_uri' => $this->getUri_spinomenal(),
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
	
	public function spinomenal_insert_bet($input, $token, $user_wealth, $current_balance)
	{
		
		$bet_id = $this->insert_bet([
			'balance_before' 	   => $user_wealth->balance,
			'balance_after' 	   => $current_balance,
			'bet_sum'			   => ($input['BetAmount']/100),
			'tx_id' 			   => $input['TicketId'],
			'external_session_id'  => $input['RoundId'],
			'internal_session_id'  => $token->id,
			'profit' 			   => ($input['BetAmount']/100),
			'user_id' 			   => $token->user_id,
			'game_id' 			   => $token->game_id,
			'created_at' 		   => date('Y-m-d H:i:s'),
			'bet_at' 			   => date('Y-m-d H:i:s.u'), 
			'wallet_id' 		   => $user_wealth->id,
			'status' 			   => 0,
		]);	
		
		return $bet_id;
		
	}


	public function spinomenal_insert_transaction($input, $token, $user_wealth, $bet_id, $sign=1, $amount_type = 'BetAmount')
	{
		
		$transaction_id = $this->insert_transaction([
			'amount' 	   => $sign*($input[$amount_type]/100),
			'bonus_part'   => 0,
			'currency_id'  => $user_wealth->currency_id,
			'reference_id' => $bet_id,
			'reference_type_id' => 4,
			'player_id'    => $token->user_id,
			'type_id'	   => 1,
			'created_at'   => date('Y-m-d H:i:s')
		]);
		
		return $transaction_id;
		
	}

    public function sig_hash_spinomenal(){
        return md5(join('', func_get_args()));
    }

    public function jsonResponse_spinomenal(object $json)
    {
        return json_decode($json->getBody()->getContents());
    }

    private function handlerError_spinomenal(object $response)
    {
        if (isset($response->ErrorCode) && isset($response->ErrorMessage) && !empty($response->ErrorCode) && !empty($response->ErrorMessage)) {
            throw new Exception("Oops, $response->ErrorMessage, $response->ErrorCode");
        }
    }

    private function customError_spinomenal(array $checkFields, object $response, string $code){
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
