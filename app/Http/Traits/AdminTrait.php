<?php
namespace App\Http\Traits;

trait AdminTrait {
		
	public function gender_normalize($numeric) {
		
		return $numeric==1?'Female':'Male';		
		
	}	
	
	public function get_limit_options() {
		
		$options = [
			'cooling_off_options' => [
				[
					'value' => 1,
					'label' => '1 Day',
				],
				[
					'value' => 2,
					'label' => '3 Days',
				],
				[
					'value' => 3,
					'label' => '1 Week',
				],
				[
					'value' => 4,
					'label' => '1 Month',
				],
				[
					'value' => 5,
					'label' => '3 Months',
				],
				[
					'value' => 6,
					'label' => '6 Months',
				],
			],
			'self_exclusion_options' => [
				[
					'value' => 7,
					'label' => '24 Hours',
				],
				[
					'value' => 8,
					'label' => '7 Days',
				],
				[
					'value' => 9,
					'label' => '14 Days',
				],
				[
					'value' => 10,
					'label' => '1 Month',
				],
				[
					'value' => 11,
					'label' => '3 Months',
				],
				[
					'value' => 12,
					'label' => '6 Months',
				],
				[
					'value' => 13,
					'label' => '9 Months',
				],
				[
					'value' => 14,
					'label' => '1 Year',
				],
				[
					'value' => 15,
					'label' => '3 Years',
				],
				[
					'value' => 16,
					'label' => 'Forever',
				],
			],
			'deposit_limit_options' => [
				[
					'value' => 17,
					'label' => '1 Day',
				],
				[
					'value' => 18,
					'label' => '1 Week',
				],
				[
					'value' => 19,
					'label' => '1 Month',
				],
			],
			'loss_limit_options' => [
				[
					'value' => 20,
					'label' => '1 Day',
				],
				[
					'value' => 21,
					'label' => '1 Week',
				],
				[
					'value' => 22,
					'label' => '1 Month',
				],
			],
			'wager_limit_options' => [
				[
					'value' => 23,
					'label' => '1 Day',
				],
				[
					'value' => 24,
					'label' => '1 Week',
				],
				[
					'value' => 25,
					'label' => '1 Month',
				],
			],
		];
		
		return $options;
		
	}
	
	public function compose_dummy_package($inserted_array=[]) {
		
		$initial_array = [
			'tableData' => $inserted_array['tableData'],					
			'tableColumns' => $inserted_array['tableColumns'],
			'tableMainFilters' => [],
			'filters' => [],
			'hiddenFilters' => [],
			'filterLinks' => [],
			'maxPage' => 1,
			'defaultMaxPage' => 1,
			'offset' => 21,
			'defaultOffset' => 20,
			'createUrl' => 'http://housecasino.com/admin/players/create',
			'storeUrl' => 'http://housecasino.com/admin/players',
			'publishUrl' => 'http://housecasino.com/admin/players/publish',
			'bulkPublishUrl' => 'http://housecasino.com/admin/players/bulkPublish',
			'restoreUrl' => 'http://housecasino.com/admin/players/restore',
			'bulkRestoreUrl' => 'http://housecasino.com/admin/players/bulkRestore',
			'forceDeleteUrl' => 'http://housecasino.com/admin/players/forceDelete',
			'bulkForceDeleteUrl' => 'http://housecasino.com/admin/players/bulkForceDelete',
			'reorderUrl' => false,
			'featureUrl' => false,
			'bulkFeatureUrl' => false,
			'bulkDeleteUrl' => 'http://housecasino.com/admin/players/bulkDelete',
			'moduleName' => 'players',
			'skipCreateModal' => false,
			'reorder' => false,
			'create' => 1,
			'duplicate' => false,
			'translate' => false,
			'translateTitle' => false,
			'permalink' => 1,
			'bulkEdit' => 1,
			'titleFormKey' => 'email',
			'baseUrl' => 'http://housecasino.com/players/',
			'permalinkPrefix' => 'housecasino.com/players/',	
		];	

		if(isset($inserted_array['indexUrl'])) {
			$initial_array['indexUrl'] = $inserted_array['indexUrl'];
		}
		
		return $initial_array;
		
	}
	
	public function compose_data($data) {
	
		return json_decode(json_encode($data), true);
	
	}
	
	public function compose_columns($type) {
		
		switch($type) {
		case 'player_bonus':
			return [['name'=>'published','label'=>'Published', 'visible'=> 1], ['name'=>'title','label'=>'Title', 'visible'=> 1],['name'=>'stage','label'=>'Stage', 'visible'=> 1],
					['name'=>'email','label'=>'User', 'visible'=> 1, 'html' => 1],['name'=>'currency','label'=>'Currency', 'visible'=> 1],['name'=>'amount','label'=>'Amount', 'visible'=> 1],
					['name'=>'wager','label'=>'Wager', 'visible'=> 1],['name'=>'created_at','label'=>'Issued on', 'visible'=> 1],['name'=>'expiry_date','label'=>'Expiry date', 'visible'=> 1]];
		break;
		case 'player_limits':
			return [['name'=>'id','label'=>'ID', 'visible'=> 1],['name'=>'type_name','label'=>'Type name', 'visible'=> 1],
					['name'=>'status','label'=>'Status', 'visible'=> 1],['name'=>'period_name','label'=>'Period', 'visible'=> 1],['name'=>'account_limits','label'=>'Account limits', 'visible'=> 1],
					['name'=>'current_values','label'=>'Current values', 'visible'=> 1],['name'=>'amount','label'=>'Amount', 'visible'=> 1],['name'=>'confirm_until','label'=>'Confirm until', 'visible'=> 1],
					['name'=>'created_at','label'=>'Created at', 'visible'=> 1],['name'=>'disabled_at','label'=>'Disabled at', 'visible'=> 1]
					];			
		break;
		case 'player_sessions':
			return [['name'=>'email','label'=>'User', 'visible'=> 1, 'html' => 1],['name'=>'user_agent','label'=>'User agent', 'visible'=> 1],['name'=>'device_type','label'=>'Device Type', 'visible'=> 1],
					['name'=>'ip','label'=>'IP', 'visible'=> 1],['name'=>'country','label'=>'Country', 'visible'=> 1],['name'=>'created_at','label'=>'Created at', 'visible'=> 1]
					];			
		break;
		case 'player_suspicions':
			return [['name'=>'email','label'=>'Suspect', 'visible'=> 1, 'html' => 1],['name'=>'suspicion_name','label'=>'Reason', 'visible'=> 1],['name'=>'created_at','label'=>'Created at', 'visible'=> 1],
					['name'=>'updated_at','label'=>'Updated at', 'visible'=> 1]
					];			
		break;
		case 'all_suspicions':
			return [['name'=>'email','label'=>'Suspect', 'visible'=> 1, 'html' => 1],['name'=>'suspicion_name','label'=>'Reason', 'visible'=> 1],['name'=>'created_at','label'=>'Created at', 'visible'=> 1],
					['name'=>'updated_at','label'=>'Updated at', 'visible'=> 1]
					];			
		break;
		case 'all_payments':
			return [['name'=>'email','label'=>'Suspect', 'visible'=> 1, 'html' => 1],['name'=>'amount','label'=>'Amount', 'visible'=> 1],['name'=>'currency','label'=>'Currency', 'visible'=> 1],
					['name'=>'player_action','label'=>'Action', 'visible'=> 1],['name'=>'source','label'=>'Source', 'visible'=> 1],['name'=>'success','label'=>'Success', 'visible'=> 1],
					['name'=>'comments','label'=>'Comments', 'visible'=> 1],['name'=>'finished_at','label'=>'Finished at', 'visible'=> 1],['name'=>'admin_id','label'=>'Admin user', 'visible'=> 1]
					];			
		break;
		case 'player_game_stats':
			return [['name'=>'title','label'=>'Game', 'visible'=> 1, 'html' => 1],['name'=>'currency','label'=>'Currency', 'visible'=> 1],['name'=>'total_winnings','label'=>'Total winnings', 'visible'=> 1],
					['name'=>'total_loss','label'=>'Total loss', 'visible'=> 1],['name'=>'profit','label'=>'Profit', 'visible'=> 1],['name'=>'payout','label'=>'Payout', 'visible'=> 1],
					['name'=>'game_id','label'=>'View', 'visible'=> 1]
					];			
		break;
		case 'player_game_bets':
			return [['name'=>'title','label'=>'Game', 'visible'=> 1, 'html' => 1],['name'=>'currency','label'=>'Currency', 'visible'=> 1],['name'=>'balance_before','label'=>'Balance before', 'visible'=> 1],
					['name'=>'balance_after','label'=>'Balance after', 'visible'=> 1],['name'=>'bets_sum','label'=>'Bets sum', 'visible'=> 1],['name'=>'payoffs_sum','label'=>'Payoffs sum', 'visible'=> 1],
					['name'=>'profit','label'=>'Profit', 'visible'=> 1],['name'=>'bonus_issue','label'=>'Bonus issue', 'visible'=> 1],['name'=>'jackpot_win','label'=>'Jackpot Win', 'visible'=> 1],
					['name'=>'email','label'=>'Player', 'visible'=> 1],['name'=>'bet_at','label'=>'Created at', 'visible'=> 1],['name'=>'finished_at','label'=>'Finished at', 'visible'=> 1]
					];			
		break;
		case 'player_events':
			return [['name'=>'subject','label'=>'Subject', 'visible'=> 1, 'html' => 1],['name'=>'event_name','label'=>'Event', 'visible'=> 1],['name'=>'ip','label'=>'IP', 'visible'=> 1],
					['name'=>'created_at','label'=>'Created at', 'visible'=> 1],['name'=>'coordinates','label'=>'Coordinates', 'visible'=> 1],['name'=>'country','label'=>'Country', 'visible'=> 1],
					['name'=>'address','label'=>'Address', 'visible'=> 1]
					];			
		break;
		case 'game_sessions':
			return [['name'=>'email','label'=>'User', 'visible'=> 1, 'html' => 1],['name'=>'active','label'=>'Active', 'visible'=> 1],['name'=>'ident','label'=>'Game Identifier', 'visible'=> 1],
					['name'=>'last_active_at','label'=>'Last activity at', 'visible'=> 1],['name'=>'created_at','label'=>'Created at', 'visible'=> 1]
					];			
		break;
		}
		
		return [];
		
	}
	
	public function compose_player_links($data, $view, $id='id', $link_target='email') {
		
		foreach($data as $key=>$value) {
			$data[$key][$link_target] = str_replace(["::href::", "::anchor::"], ["/admin/players/".$value[$id]."/view", $value['email']], $view);
		}
		
		return $data;
		
	}

	
}