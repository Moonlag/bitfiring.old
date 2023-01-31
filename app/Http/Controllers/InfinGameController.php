<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use App\Http\Traits\DBTrait;
use App\Http\Traits\AuthTrait;
use Illuminate\Database\Query\Expression;
use App\Http\Traits\CallBackTrait;
use App\Http\Traits\BonusTrait;
use App\Http\Traits\ServiceTrait;
use Spatie\ArrayToXml\ArrayToXml;

class InfinGameController extends Controller
{
	use DBTrait;
	use AuthTrait;
	use CallBackTrait;
	use BonusTrait;
	use ServiceTrait;
	
    private $authToken = '';

    public function __construct()
    {
		
		
        $this->middleware(function ($request, $next) {
			
			$input = $request->all();
			//$passed = $this->check_signature($input);
			$xml_data = $request->getContent();
			
			$data = json_decode(file_get_contents('php://input'), true);
			$this->insert_callback('infingame', json_encode($request->all()).json_encode($data).json_encode($_SERVER).$xml_data, json_encode($request->header()));	
			
			/* 
		 	if(!$passed) {
				
				$error = $this->show_error(1003, "Authentication failed", 200, "infingame", $input);
				$result = ArrayToXml::convert($error['array'], $error['root']);
				return response($result, 200)->header('Content-Type', 'application/xml');			
			}  */
			
			return $next($request);
			
        });
    }
	
    private function check_signature($input)
    {
      	
		$key_array = ["signature", "ipinfo"];
		$uri = "";
		foreach($input as $key=>$part) {
			if(!in_array($key, $key_array)) {
				if(strlen($part) > 0) {
					$uri .= "&".$key."=".$part;
				} else {
					$uri .= "&".$key;
				}
			}
		}
		$uri = substr($uri, 1);
		
		$uri_md5 = md5($uri."m5iPmsf4IqPsT9bvpZb5Evm2PPIoC4");
		
		if(strtoupper($uri_md5) != $input['signature']) {
			return 0;
		}
		
		return 1;
		
    }	
	
	public function check_request_duplicate($input, $data, $key) {
		
		$duplicate = $this->get_request_duplicate($input['@attributes']['id']);
		if ($duplicate) {
			return 1;
		} else {
			$input = $this->insert_request_duplicate($input['@attributes']['id']);
			return 0;
		}
		
	}
	
    public function process(Request $request)
    {
		
		$xml = $request->getContent();
		$xml = simplexml_load_string($xml);
		$json = json_encode($xml);
        $input = json_decode($json,TRUE);
				
		$result = "";
		
		foreach($input as $key=>$value) {
			
			$key = $key=="re-enter"?"reenter":$key;
			
			if(method_exists($this, $key)) {
				$duplicate = $this->check_request_duplicate($value, $input, $key);
				if ($duplicate) {
					$result = $this->exit_process($value, $input, $key);
				} else {
					$result = $this->$key($value, $input, $key);
				}
			}
			if($result) break;
		}
		
		return response($result, 200)->header('Content-Type', 'application/xml');
		
    }


    public function roundbet($input, $data, $key)
    {
		
		$input['element'] = $key;
	 
		$root_xml = [
			'rootElementName' => 'service',
			'_attributes' => [
					'session' => $data['@attributes']['session'],
					'time' 	  => $data['@attributes']['time'],
			],	
		];
		
		$bet = $this->get_bet(0,0,0,0,[["tx_id",'=',$input['roundnum']['@attributes']['id']],["external_session_id","=",$input['@attributes']['id']]]);

		$user_status = $this->get_user_status($input['@attributes']['wlid']);
		
		if(!isset($user_status->status) || $user_status->status != 1) {
			$error = $this->show_error("USER_BLOCKED", "Player blocked", 200, "infingame", $input);
			$result = ArrayToXml::convert($error, $root_xml, true, 'UTF-8');
			return $result;
		}

		$game = $this->get_game_session("", "", 0, [["user_id", "=", $input['@attributes']['wlid']]]);
		
		if (!$game) {
			$error = $this->show_error("WL_ERROR", "Game not found", 200, "infingame", $input);
			$result = ArrayToXml::convert($error, $root_xml, true, 'UTF-8');
			return $result;
		}
		
		$user_wealth = $this->get_user_wallet($game->user_id, $game->wallet_id, $game->game_id);
		$denomination = $this->get_denomination($user_wealth->currency_id, $game->provider_id);	

		$input['withdraw'] = $input['@attributes']['bet'];
		
		if($user_wealth->balance < ($input['withdraw']*$denomination->denomination)) {
			$error = $this->show_error("NOT_ENOUGH_MONEY", "Not enough money. Please, consider a deposit", 200, "infingame", $input);
			$result = ArrayToXml::convert($error, $root_xml, true, 'UTF-8');
			return $result;
		}
		
		if(!$bet) {
			
			$current_balance = ($user_wealth->balance-($input['withdraw']*$denomination->denomination));
			$bet_id = $this->add_bet($input, $user_wealth, $game, "infingame", $denomination);
			$tx_id  = $this->add_transaction($input, $user_wealth, $game, "infingame", $bet_id, -1, "withdraw", $denomination);
			$this->update_balance($game->user_id, $user_wealth->id, -($input['withdraw']*$denomination->denomination));
			$this->update_fixed_data($game, $user_wealth, -$input['withdraw']*$denomination->denomination);		
			$this->update_bonus_data($game, $user_wealth, $input['withdraw']*$denomination->denomination);			
			$this->update_bet(['status' => 1], $bet_id, "id");
			
		} else {
			
			$current_balance = ($user_wealth->balance-($input['withdraw']*$denomination->denomination));
			$tx_id  = $this->add_transaction($input, $user_wealth, $game, "infingame", $bet->id, -1, "withdraw", $denomination);
			$this->update_balance($game->user_id, $user_wealth->id, -($input['withdraw']*$denomination->denomination));
			$this->update_fixed_data($game, $user_wealth, -$input['withdraw']*$denomination->denomination);		
			$this->update_bonus_data($game, $user_wealth, $input['withdraw']*$denomination->denomination);			
			$this->update_bet(['status' => 1], $bet->id, "id");			
			
		}
		
		$user_wealth = $this->get_user_wallet($game->user_id, $game->wallet_id, $game->game_id);
		$denomination = $this->get_denomination($user_wealth->currency_id, $game->provider_id);	
			
		$xml = [ 
			'roundbet' => [				
				'_attributes' => [
					'id' => $input['@attributes']['id'],
					'result'  => "ok",
				],		
				'balance' => [				
					'_attributes' => [
						'currency' => $denomination->altercode,
						'type'     => "real",
						'value'    => floor($user_wealth->balance/$denomination->denomination),
						'version'  => time(),
					],		
				],	
			]
		];
		
		$result = ArrayToXml::convert($xml, $root_xml, false);
		$result = preg_replace('!^[^>]+>(\r\n|\n)!','',$result);
		
		return $result;
		
    }
	

	public function refund($input, $data, $key) 
	{
		
		$input['element'] = $key;
	 
		$root_xml = [
			'rootElementName' => 'service',
			'_attributes' => [
					'session' => $data['@attributes']['session'],
					'time' 	  => $data['@attributes']['time'],
			],	
		];
		
		$user_status = $this->get_user_status($input['@attributes']['wlid']);
			
		if(!isset($user_status->status) || $user_status->status != 1) {
			$error = $this->show_error("USER_BLOCKED", "Player blocked", 200, "infingame", $input);
			$result = ArrayToXml::convert($error, $root_xml, true, 'UTF-8');
			return $result;
		}	
		
		$bet = $this->get_bet(0,0,0,0,[["tx_id",'=',$input['storno']['roundnum']['@attributes']['id']],["external_session_id","=",$input['storno']['@attributes']['id']]]);
		
		if($bet) {
			$rollback 	 = $this->get_rollback("bet_id", $bet->id, 1);
			$transactions = $this->get_transactions(0, 0, 0, 0, [["reference_id","=",$bet->id],["type_id","!=",2]]);		
		}
		
		if($bet && !$rollback && $transactions) {

			foreach($transactions as $transaction) {
				$reverted_id = $this->revert_transaction($transaction);
			}
			
		}
		
		$game = $this->get_game_session("", "", 0, [["user_id", "=", $input['@attributes']['wlid']],["ident", "=", $input['storno']['@attributes']['gameid']]]);
		$user_wealth = $this->get_user_wallet($input['@attributes']['wlid'], $game->wallet_id, $game->game_id);
		$denomination = $this->get_denomination($user_wealth->currency_id, $game->provider_id);		
		
		$xml = [ 
			'refund' => [				
				'_attributes' => [
					'id' => $input['@attributes']['id'],
					'result'  => "ok",
				],		
				'balance' => [				
					'_attributes' => [
						'currency' => $denomination->altercode,
						'type'     => "real",
						'value'    => floor($user_wealth->balance/$denomination->denomination),
						'version'  => time(),
					],		
				],	
			]
		];
		
		$result = ArrayToXml::convert($xml, $root_xml, false);
		$result = preg_replace('!^[^>]+>(\r\n|\n)!','',$result);
		
		return $result;
		
	}		
	
	public function roundwin($input, $data, $key) 
	{
		
		$input['element'] = $key;
	 
		$root_xml = [
			'rootElementName' => 'service',
			'_attributes' => [
					'session' => $data['@attributes']['session'],
					'time' 	  => $data['@attributes']['time'],
			],	
		];	
		
		$bet = $this->get_bet(0,0,0,0,[["tx_id",'=',$input['roundnum']['@attributes']['id']]]);
		
		$game = $this->get_game_session("", "", 0, [["user_id", "=", $input['@attributes']['wlid']]]);
		$user_wealth = $this->get_user_wallet($input['@attributes']['wlid'], $game->wallet_id, $game->game_id);
		$denomination = $this->get_denomination($user_wealth->currency_id, $game->provider_id);

		if(!$bet) {

			$xml = [ 
				'roundwin' => [				
					'_attributes' => [
						'id' => $input['@attributes']['id'],
						'result'  => "ok",
					],		
					'balance' => [				
						'_attributes' => [
							'currency' => $denomination->altercode,
							'type'     => "real",
							'value'    => floor($user_wealth->balance/$denomination->denomination),
							'version'  => time(),
						],		
					],	
				]
			];			
			$result = ArrayToXml::convert($xml, $root_xml, false);
			$result = preg_replace('!^[^>]+>(\r\n|\n)!','',$result);
			
			return $result;
		
		
		}
		
		$user_status = $this->get_user_status($input['@attributes']['wlid']);
		
		if(!isset($user_status->status) || $user_status->status != 1) {
			$error = $this->show_error("USER_BLOCKED", "Player blocked", 200, "infingame", $input);
			$result = ArrayToXml::convert($error, $root_xml, true, 'UTF-8');
			return $result;
		}

		$game = $this->get_game_session("", "", 0, [["user_id", "=", $input['@attributes']['wlid']]]);
		
		if (!$game) {
			$error = $this->show_error("WL_ERROR", "Game not found", 200, "infingame", $input);
			$result = ArrayToXml::convert($error, $root_xml, true, 'UTF-8');
			return $result;
		}
		
		$input['result'] = $input['@attributes']['win'];
		
		$current_balance = $user_wealth->balance+($input['result']*$denomination->denomination);
		$tx_id = $this->add_transaction($input, $user_wealth, $game, "infingame", $bet->id, 1, "result", $denomination);
		$this->update_bet(['balance_after' => $current_balance,	'payoffs_sum' => ($input['result']*$denomination->denomination), 'profit'=>new Expression('profit - '.($input['result']*$denomination->denomination)), 'updated_at' => date('Y-m-d H:i:s'), 'status' => 3 ], $bet->id, "id");
		$this->update_balance($game->user_id, $user_wealth->id, ($input['result']*$denomination->denomination));
		$this->update_fixed_data($game, $user_wealth, $input['result']*$denomination->denomination);

		$user_wealth = $this->get_user_wallet($game->user_id, $game->wallet_id, $game->game_id);
		$denomination = $this->get_denomination($user_wealth->currency_id, $game->provider_id);	
		
		$xml = [ 
			'roundwin' => [				
				'_attributes' => [
					'id' => $input['@attributes']['id'],
					'result'  => "ok",
				],		
				'balance' => [				
					'_attributes' => [
						'currency' => $denomination->altercode,
						'type'     => "real",
						'value'    => floor($user_wealth->balance/$denomination->denomination),
						'version'  => time(),
					],		
				],	
			]
		];			
		$result = ArrayToXml::convert($xml, $root_xml, false);
		$result = preg_replace('!^[^>]+>(\r\n|\n)!','',$result);
		
		return $result;	
		
		
	}
	
	
    public function exit_process($input, $data, $key)
    {
		
		$input['element'] = $key;
	
		$root_xml = [
			'rootElementName' => 'service',
			'_attributes' => [
					'session' => $data['@attributes']['session'],
					'time' 	  => $data['@attributes']['time'],
			],	
		];	
		
		if(!isset($input['@attributes']['wlid'])) {				
			$game = $this->get_game_session("token", $input['@attributes']['key']);
		} else {
			$game = $this->get_game_session("user_id", $input['@attributes']['wlid']);
		}
		$user_status = $this->get_user_status($game->user_id);
		$user_wealth = $this->get_user_wallet($game->user_id, $game->wallet_id, $game->game_id);

		if (!$game) {
			$error = $this->show_error("KEY_EXPIRED", "The authentication key expired or can not be used anymore", 200, "infingame", $input);
			$result = ArrayToXml::convert($error, $root_xml, true, 'UTF-8');
			return $result;
		}
		
		if(!isset($user_status->status) || $user_status->status != 1) {
			$error = $this->show_error("USER_BLOCKED", "Player blocked", 200, "infingame", $input);
			$result = ArrayToXml::convert($error, $root_xml, true, 'UTF-8');
			return $result;
		}
		
		$denomination = $this->get_denomination($user_wealth->currency_id, $game->provider_id);
		
		$xml = [ 
			$key => [				
				'_attributes' => [
					'id' => $input['@attributes']['id'],
					'result'  => "ok",
				],		
				'balance' => [				
					'_attributes' => [
						'currency' => $denomination->altercode,
						'type'     => "real",
						'value'    => floor($user_wealth->balance/$denomination->denomination),
						'version'  => time(),
					],		
				],	
			]
		];
		
		$result = ArrayToXml::convert($xml, $root_xml, false);
		$result = preg_replace('!^[^>]+>(\r\n|\n)!','',$result);
		
		return $result;
		
    }	
	
    public function enter($input, $data, $key)
    {

		$input['element'] = $key;
	
		$root_xml = [
			'rootElementName' => 'service',
			'_attributes' => [
					'session' => $data['@attributes']['session'],
					'time' 	  => $data['@attributes']['time'],
			],	
		];
	
		$game = $this->get_game_session("token", $input['@attributes']['key']);

		if (!$game) {
			$error = $this->show_error("KEY_EXPIRED", "The authentication key expired or can not be used anymore", 200, "infingame", $input);
			$result = ArrayToXml::convert($error, $root_xml, true, 'UTF-8');
			return $result;
		}		
		
		$user_status = $this->get_user_status($game->user_id);
		$user_wealth = $this->get_user_wallet($game->user_id, $game->wallet_id, $game->game_id);
		
		if(!isset($user_status->status) || $user_status->status != 1) {
			$error = $this->show_error("USER_BLOCKED", "Player blocked", 200, "infingame", $input);
			$result = ArrayToXml::convert($error, $root_xml, true, 'UTF-8');
			return $result;
		}
		
		$denomination = $this->get_denomination($user_wealth->currency_id, $game->provider_id);
		
		$xml = [
			'enter' => [				
				'_attributes' => [
					'id' => $input['@attributes']['id'],
					'result'  => "ok",
				],		
				'balance' => [				
					'_attributes' => [
						'currency' => $denomination->altercode,
						'type'     => "real",
						'value'    => floor($user_wealth->balance/$denomination->denomination),
						'version'  => time(),
					],		
				],
				'user'   => [				
					'_attributes' => [
						'type'     => "real",
						'wlid' 	   => $game->user_id,
					],					
				],			
			]
		];
		
		$result = ArrayToXml::convert($xml, $root_xml, false);
		$result = preg_replace('!^[^>]+>(\r\n|\n)!','',$result);
		
		return $result;
		
    }
	
    public function reenter($input, $data, $key)
    {

		$input['element'] = $key;
	
		$root_xml = [
			'rootElementName' => 'service',
			'_attributes' => [
					'session' => $data['@attributes']['session'],
					'time' 	  => $data['@attributes']['time'],
			],	
		];
	
		$game = $this->get_game_session("user_id", $input['@attributes']['wlid']);
		$user_status = $this->get_user_status($game->user_id);
		$user_wealth = $this->get_user_wallet($game->user_id, $game->wallet_id, $game->game_id);
		
		if(!isset($user_status->status) || $user_status->status != 1) {
			$error = $this->show_error("USER_BLOCKED", "Player blocked", 200, "infingame", $input);
			$result = ArrayToXml::convert($error, $root_xml, true, 'UTF-8');
			return $result;
		}
		
		if (!$game) {
			$error = $this->show_error("KEY_EXPIRED", "The authentication key expired or can not be used anymore", 200, "infingame", $input);
			$result = ArrayToXml::convert($error, $root_xml, true, 'UTF-8');
			return $result;
		}
		
		$denomination = $this->get_denomination($user_wealth->currency_id, $game->provider_id);
		
		$xml = [
			're-enter' => [				
				'_attributes' => [
					'id' => $input['@attributes']['id'],
					'result'  => "ok",
				],		
				'balance' => [				
					'_attributes' => [
						'currency' => $denomination->altercode,
						'type'     => "real",
						'value'    => floor($user_wealth->balance/$denomination->denomination),
						'version'  => time(),
					],		
				],
				'user'   => [				
					'_attributes' => [
						'type'     => "real",
						'wlid' 	   => $game->user_id,
					],					
				],			
			]
		];
		
		$result = ArrayToXml::convert($xml, $root_xml, false);
		$result = preg_replace('!^[^>]+>(\r\n|\n)!','',$result);
		
		return $result;
		
    }	

    public function getbalance($input, $data, $key)
    {
		
		$input['element'] = $key;
	
		$root_xml = [
			'rootElementName' => 'service',
			'_attributes' => [
					'session' => $data['@attributes']['session'],
					'time' 	  => $data['@attributes']['time'],
			],	
		];	
		$game = $this->get_game_session("user_id", $input['@attributes']['wlid']);
		$user_status = $this->get_user_status($game->user_id);
		$user_wealth = $this->get_user_wallet($game->user_id, $game->wallet_id, $game->game_id);

		if (!$game) {
			$error = $this->show_error("KEY_EXPIRED", "The authentication key expired or can not be used anymore", 200, "infingame", $input);
			$result = ArrayToXml::convert($error, $root_xml, true, 'UTF-8');
			return $result;
		}
		
		if(!isset($user_status->status) || $user_status->status != 1) {
			$error = $this->show_error("USER_BLOCKED", "Player blocked", 200, "infingame", $input);
			$result = ArrayToXml::convert($error, $root_xml, true, 'UTF-8');
			return $result;
		}
		
		$denomination = $this->get_denomination($user_wealth->currency_id, $game->provider_id);
		
		$xml = [ 
			'getbalance' => [				
				'_attributes' => [
					'id' => $input['@attributes']['id'],
					'result'  => "ok",
				],		
				'balance' => [				
					'_attributes' => [
						'currency' => $denomination->altercode,
						'type'     => "real",
						'value'    => floor($user_wealth->balance/$denomination->denomination),
						'version'  => time(),
					],		
				],	
			]
		];
		
		$result = ArrayToXml::convert($xml, $root_xml, false);
		$result = preg_replace('!^[^>]+>(\r\n|\n)!','',$result);
		
		return $result;
		
    }
	
	

    public function logout($input, $data, $key)
    {
		
		$input['element'] = $key;
	
		$root_xml = [
			'rootElementName' => 'service',
			'_attributes' => [
					'session' => $data['@attributes']['session'],
					'time' 	  => $data['@attributes']['time'],
			],	
		];	
		$game = $this->get_game_session("user_id", $input['@attributes']['wlid']);
		$user_status = $this->get_user_status($game->user_id);
		$user_wealth = $this->get_user_wallet($game->user_id, $game->wallet_id, $game->game_id);
		
		if (!$game) {
			$error = $this->show_error("KEY_EXPIRED", "The authentication key expired or can not be used anymore", 200, "infingame", $input);
			$result = ArrayToXml::convert($error, $root_xml, true, 'UTF-8');
			return $result;
		}
		
		if(!isset($user_status->status) || $user_status->status != 1) {
			$error = $this->show_error("USER_BLOCKED", "Player blocked", 200, "infingame", $input);
			$result = ArrayToXml::convert($error, $root_xml, true, 'UTF-8');
			return $result;
		}
		
		$denomination = $this->get_denomination($user_wealth->currency_id, $game->provider_id);
		
		$xml = [ 
			'logout' => [				
				'_attributes' => [
					'id' => $input['@attributes']['id'],
					'result'  => "ok",
				],		
				'balance' => [				
					'_attributes' => [
						'currency' => $denomination->altercode,
						'type'     => "real",
						'value'    => floor($user_wealth->balance/$denomination->denomination),
						'version'  => time(),
					],		
				],	
			]
		];
		
		$result = ArrayToXml::convert($xml, $root_xml, false);
		$result = preg_replace('!^[^>]+>(\r\n|\n)!','',$result);
		
		return $result;
		
    }
	
}
