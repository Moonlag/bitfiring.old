<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Mail;
use Response;
use Corcel;
use LaravelLocalization;
use App\Http\Traits\ServiceTrait;
use App\Http\Traits\LanguageTrait;
use App\Http\Traits\AuthTrait;
use App\Http\Traits\DBTrait;
use App\Http\Traits\RoutineTrait;
use Intervention\Image\ImageManagerStatic as Image;
use Illuminate\Support\Str;


use App\Http\Traits\PragmaticTrait;
use App\Http\Traits\BelatraTrait;
use App\Http\Traits\MascotTrait;
use App\Http\Traits\BoomingTrait;
use App\Http\Traits\EvoplayTrait;
use App\Http\Traits\RelaxGamingTrait;
use App\Http\Traits\SpinomenalTrait;
use App\Http\Traits\ZillionTrait;
use App\Http\Traits\IgrosoftTrait;
use App\Http\Traits\SlottyTrait;
use App\Http\Traits\OnlyplayTrait;
use App\Http\Traits\BgamingTrait;
use App\Http\Traits\KAgamingTrait;
use App\Http\Traits\InfinGamingTrait;
use App\Http\Traits\CustomTrait;

class OfficeController extends Controller
{

	use DBTrait;
	use CustomTrait;
	use BelatraTrait;
	use MascotTrait;
	use BoomingTrait;
	use EvoplayTrait;
	use RelaxGamingTrait;
	use SpinomenalTrait;
	use ZillionTrait;
	use IgrosoftTrait;
	use SlottyTrait;
	use OnlyplayTrait;
	use BgamingTrait;
	use KAgamingTrait;
	use InfinGamingTrait;
	use PragmaticTrait;

	public function test_request() {
		
		echo 'hi';
		exit;
		
	}
	
	public function refresh_slots_1spin4win() {


		$provider = "onespinforwin";
		$provider_id = 47;
		
		$file = fopen($_SERVER['DOCUMENT_ROOT'].'/storage/app/public/onespinforwin.csv', "r");
		$all_data = array();
		
		while (($game = fgetcsv($file, 1400, ",")) !== false) {

			$result = $this->check_game($provider, $game[5]);
			
			if(!isset($result->id)) {
				
				$game_type = $this->get_game_type("slot");
				
				if(!isset($game_type->game_type_id)) {
					print_r($game);
					echo 'no game type found. halting..';
					exit;
				}
				
				$img_local_save_path = 'uploads/' . $provider . '/'  .  $game[5] . '.webp';
								
				$game_slug =  Str::slug($game[2]);
				$this->insert_game($game[5], $game[2], $provider, $game_type->game_type_id, $img_local_save_path, $game_slug, $provider_id);

				echo 'game ' . $game[2] . ' inserted<br/>';

			} else {

				$game_type = $this->get_game_type("slot");
				$game_slug = Str::slug($game[2]);

				$this->update_game($game[5], $game[2], $result->id, $provider, $game_type->game_type_id, $game_slug, $provider_id);
				
			}			
		
		}		
	
		echo 'hi';
		exit;
		
	}
	

	public function refresh_slots_pragmaticplay(Request $request)
	{

		$provider = "pragmatic";
		$provider_id = 48;
		
		$this->prepare_pragmaticplay("https://api.prerelease-env.biz/IntegrationService/v3/http/CasinoGameAPI/","testKey");
		
		$json = $this->getGameList_pragmaticplay([
			'secureLogin' => 'hscs_bitfiring',
		])->getBody();
		$hash =  md5($json);
		$data = json_decode($json);
		
		$result = $this->check_hash($hash, $provider);
		
		if(isset($result->id)) {
			echo 'hash matches the DB';
			exit;
		}
		foreach($data->gameList as $key=>$game) {
			
			$result = $this->check_game($provider, $game->gameID);
			
			if(!isset($result->id)) {
				
				$game_type = $this->get_game_type("slot");
				
				if(!isset($game_type->game_type_id)) {
					print_r($game);
					echo 'no game type found. halting..';
					exit;
				}
				
				$img_local_save_path = 'uploads/' . $provider . '/'  .  $game->gameID . '.webp';
								
				$game_slug =  Str::slug($game->gameName);
				
				$this->insert_game($game->gameID, $game->gameName, $provider, $game_type->game_type_id, $img_local_save_path, $game_slug, $provider_id);

				echo 'game ' . $game->gameName . ' inserted<br/>';

			} else {

				$game_type = $this->get_game_type("slot");
				$game_slug = Str::slug($game->gameName);

				$this->update_game($game->gameID, $game->gameName, $result->id, $provider, $game_type->game_type_id, $game_slug, $provider_id);
				
			}
			
		}
				
	}	
	
    public function export_this()  
    {
		
		$fileName = 'tasks.csv';
		$tasks = $this->get_specific_games();
		
        $headers = array(
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        );

        $columns = array('Name', 'Image');

        $callback = function() use($tasks, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
			
			$row = [];
			
            foreach ($tasks as $task) {
                $row['Name']  = $task->Name;
                $row['Image'] = $task->Image;

                fputcsv($file, array($row['Name'], $row['Image']));
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
		
	}		
	
    public function export_this_payments()  
    {
		
		$fileName = 'tasks.csv';
		$tasks = $this->get_specific_payments();
		
        $headers = array(
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        );

        $columns = array('Email', 'Player id', 'Amount',  'Amount USD', 'Payment Created At');

        $callback = function() use($tasks, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
			
			$row = [];
			
            foreach ($tasks as $task) {
                $row['Email']  = $task->email;
                $row['Player id']    = $task->player_id;
                $row['Amount']    = $task->amount?$task->amount:0;
                $row['Amount USD']    = $task->amount?$task->amount:0;
                $row['Bonus Created At']  = $task->payment_created?$task->payment_created:0;

                fputcsv($file, array($row['Email'], $row['Player id'], $row['Amount'], $row['Amount USD'], $row['Bonus Created At']));
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
		
	}	
	
    public function export_this_bonuses()  
    {
		
		$fileName = 'tasks.csv';
		$tasks = $this->get_specific_bonuses();
		
        $headers = array(
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        );

        $columns = array('Email', 'Player id', 'Amount', 'Bonus Created At');

        $callback = function() use($tasks, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
			
			$row = [];
			
            foreach ($tasks as $task) {
                $row['Email']  = $task->email;
                $row['Player id']    = $task->player_id;
                $row['Amount']    = $task->amount?$task->amount:0;
                $row['Bonus Created At']  = $task->bonus_created?$task->bonus_created:0;

                fputcsv($file, array($row['Email'], $row['Player id'], $row['Amount'], $row['Bonus Created At']));
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
		
	}
	
    public function export_this_bets()  
    {
		
		$fileName = 'tasks.csv';
		$tasks = $this->get_specific_bets();

        $headers = array(
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        );

        $columns = array('Email', 'Player id', 'Bet Sum', 'Win', 'Profit', 'Partner Email', 'Bet Created At');

        $callback = function() use($tasks, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
			
			$row = [];
			
            foreach ($tasks as $task) {
                $row['Email']  = $task->email;
                $row['Player id']    = $task->player_id;
                $row['Bet Sum']    = $task->bet_sum?$task->bet_sum:0;
                $row['Win']  = $task->payoffs_sum?$task->payoffs_sum:0;
                $row['Profit']  = $task->profit;
                $row['Partner Email']  = $task->partner_email;
                $row['Bet Created At']  = $task->bet_created;

                fputcsv($file, array($row['Email'], $row['Player id'], $row['Bet Sum'], $row['Win'], $row['Profit'], $row['Partner Email'], $row['Bet Created At']));
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
		
	}

    public function refresh_slots_infingaming(Request $request)  
    {
		
		
		//$test = file_get_contents("https://dev-staging.infingame.com/admservice?login=bitfiring&password=c4A8GT2Z2aykk96X&format=xml&cm=games&enabled=1&producer=belatra");

        $provider = "infingames";
        $img_url = "";
		
        $this->prepare_InfinGaming("https://dev-staging.infingame.com/admservice", "c4A8GT2Z2aykk96X");
		$xml = $this->getgamelist_InfinGaming([
							"login" 	=> "bitfiring",
							"password"  => "c4A8GT2Z2aykk96X",
							"format" 	=> "xml",
							"cm" 		=> "games",
							"enabled"   => 1,
							"producer"  => "platipus",
						]);
		
	
		$xml = simplexml_load_string($xml);
        $hash = md5($xml);
		$json = json_encode($xml);
        $data = json_decode($json,TRUE);	
		
        $result = $this->check_hash($hash, $provider);
		
        if (isset($result->id)) {
            echo 'hash matches the DB';
            exit;
        }
		//$used = [];
		
        foreach ($data['game'] as $key => $game) {
			
			/*
			if(!in_array($game['producer'], $used, )) {
				echo $game['producer'];
				$used[] = $game['producer'];
				continue;
			}
			continue;
			*/
			
			$sub_provider = "platipus";
			$provider_id = 45;
            $result = $this->check_game($sub_provider, $game['name']);

			if (!isset($result->id)) {
				
				$game_type = $this->get_game_type("slot");

				if (!isset($game_type->game_type_id)) {
					print_r($game);
					echo 'no game type found. halting..';
					exit;
				}
				
				$game_slug = Str::slug($game['product-name']);
				
				$img_local_path = public_path('uploads/' . $sub_provider . '/' . $game['name'] . '.webp');
				$img_local_save_path = 'uploads/' . $sub_provider . '/' . $game['name'] . '.webp';

				$this->insert_game($game['name'], $game['product-name'], $sub_provider, $game_type->game_type_id, $img_local_save_path, $game_slug, $provider_id);

				echo 'game ' . $game['product-name']. ' inserted<br/>';
				
			} else { 

				$game_type = $this->get_game_type("slot");
				$game_slug = Str::slug($game['product-name']);

				$this->update_game($game['name'], $game['product-name'], $result->id, $sub_provider, $game_type->game_type_id, $game_slug, $provider_id);

			}
		}
		
	}
	
    public function refresh_slots_sportgames(Request $request)  
    {
		
        $provider = "sportgames";
        $img_url = "";
		
		$data = [
			[
				'title' => 'Boxing',
				'code' => 'sport-games-boxing',
			],
			[
				'title' => 'Bridge',
				'code' => 'sport-games-bridge',
			],
			[
				'title' => 'Darts',
				'code' => 'sport-games-darts',
			],
			[
				'title' => 'ShootOut',
				'code' => 'sport-games-shootout',
			],
			[
				'title' => 'Superlive',
				'code' => 'sport-games-superlive',
			],
		];
		
		$json = json_encode($data);
        $hash = md5($json);
        $data = json_decode($json);		
		
        $result = $this->check_hash($hash, $provider);
		
        if (isset($result->id)) {
            echo 'hash matches the DB';
            exit;
        }
		
		
        foreach ($data as $key => $game) {
			
			
			$sub_provider = "sportgames";
			$provider_id = 43;
            $result = $this->check_game($sub_provider, $game->code);

			if (!isset($result->id)) {

				$game_type = $this->get_game_type("slot");

				if (!isset($game_type->game_type_id)) {
					print_r($game);
					echo 'no game type found. halting..';
					exit;
				}

				$img_local_save_path = 'uploads/' . $provider . '/' . $game->code . '.webp';
				
				$game_slug = Str::slug($game->code);
				
				$this->insert_game($game->code, $game->title, $sub_provider, $game_type->game_type_id, $img_local_save_path, $game_slug, $provider_id);

				echo 'game ' . $game->title . ' inserted<br/>';

			} else {

				$game_type = $this->get_game_type("slot");
				$game_slug = Str::slug($game->title);

				$this->update_game($game->code, $game->title, $result->id, $sub_provider, $game_type->game_type_id, $game_slug, $provider_id);

			}
		}
		
	}	
	
    public function refresh_slots_kagaming(Request $request)  
    {
		
        $provider = "kagaming";
        $img_url = "";
		
        $this->prepare_kagaming("https://rmpstage.kaga88.com/kaga/", "F877B1CCB7E03C178B2E0F302B9A8FBD");
		$json = $this->kagaming_getGameList([
							"partnerName" => "BITFIRING",
							"accessKey" => "4DE1FCB1AEEED36CD31B2694B965BC8C",
							"language" => "en",
							"randomId" => time(),
						])->getBody()->getContents();
		
	
        $hash = md5($json);
        $data = json_decode($json);		
	
        $result = $this->check_hash($hash, $provider);
		
        if (isset($result->id)) {
            echo 'hash matches the DB';
            exit;
        }
		
		
        foreach ($data->games as $key => $game) {
			
			echo '<pre>';
				if(in_array("bp",$game->availableFeatures)) {
					dd($game);
				}
			echo '</pre>';
			continue;
			
			$sub_provider = "kagaming";
			$provider_id = 41;
            $result = $this->check_game($sub_provider, $game->gameId);

			if (!isset($result->id)) {

				$game_type = $this->get_game_type("slot");

				if (!isset($game_type->game_type_id)) {
					print_r($game);
					echo 'no game type found. halting..';
					exit;
				}

				$img_remote_path = $game->iconURLPrefix."&type=rectangular";
			
				$img_local_path = public_path('uploads/' . $provider . '/' . $game->gameId . '.webp');
				$img_local_save_path = 'uploads/' . $provider . '/' . $game->gameId . '.webp';


				$image_type = exif_imagetype($img_remote_path);

				if ($image_type == 3) {
					$img = imagecreatefrompng($img_remote_path);
					imagepalettetotruecolor($img);
					$image = Image::make($img)->encode('webp', 90)->save($img_local_path);
				} else {

					$image = Image::make($img_remote_path)->encode('webp', 90)->save($img_local_path);

				}
				
				$game_slug = Str::slug($game->gameName);

				$this->insert_game($game->gameId, $game->gameName, $sub_provider, $game_type->game_type_id, $img_local_save_path, $game_slug, $provider_id);

				echo 'game ' . $game->gameName . ' inserted<br/>';

			} else {

				$game_type = $this->get_game_type("slot");
				$game_slug = Str::slug($game->gameName);

				$this->update_game($game->gameId, $game->gameName, $result->id, $sub_provider, $game_type->game_type_id, $game_slug, $provider_id);

			}
		}
		
	}
	
    public function refresh_slots_bgaming(Request $request)  
    {
		
        $provider = "bgaming";
        $img_url = "";
		
        $data = \Yaml::parseFile('tmp/bgaming.yml');		
		
		//dd($data);
        foreach ($data as $key => $game) {

			$provider_id = 40;
		
            $result = $this->check_game($provider, $game['identifier']);

            if (!isset($result->id)) {

                $game_type = $this->get_game_type("slot");

                if (!isset($game_type->game_type_id)) {
                    print_r($game);
                    echo 'no game type found. halting..';
                    exit;
                }
				
				$game['icon'] = 'https://cdn.softswiss.net/i/s2/softswiss/'.$game['identifier'].'.webp';
				if($game['identifier'] == 'acceptance:test') {
					$game['icon'] = 'https://www.staticwhich.co.uk/static/images/products/no-image/no-image-available.png';
				}
				if($game['identifier'] == 'ElvisFrog') {
					$game['icon'] = 'https://cdn.softswiss.net/i/s2/softswiss/ElvisFroginVegas.webp';
				}
				if($game['identifier'] == 'HeadsTailsXY') {
					$game['icon'] = 'https://cdn.softswiss.net/i/s2/softswiss/HeadsTails.webp';
				}
				if($game['identifier'] == 'PlinkoCrypto') {
					$game['icon'] = 'https://cdn.softswiss.net/i/s2/softswiss/Plinko.webp';
				}
				if($game['identifier'] == 'RocketDiceCrypto') {
					$game['icon'] = 'https://cdn.softswiss.net/i/s2/softswiss/RocketDice.webp';
				}
				
				
                $img_remote_path = $game['icon'];
                $img_local_path = public_path('uploads/' . $provider . '/' . $game['identifier'] . '.webp');
                $img_local_save_path = 'uploads/' . $provider . '/' . $game['identifier'] . '.webp';
				
                $image_type = exif_imagetype($img_remote_path);

                if ($image_type == 3) {
                    $img = imagecreatefrompng($img_remote_path);
                    imagepalettetotruecolor($img);
                    $image = Image::make($img)->encode('webp', 90)->save($img_local_path);
                } else {

                    $image = Image::make($img_remote_path)->encode('webp', 90)->save($img_local_path);

                }

                $game_slug = Str::slug($game['title']);

                $this->insert_game($game['identifier'], $game['title'], $provider, $game_type->game_type_id, $img_local_save_path, $game_slug, $provider_id);

                echo 'game ' . $game['title'] . ' inserted<br/>';

            } else {

                $game_type = $this->get_game_type("slot");
                $game_slug = Str::slug($game['title']);

                $this->update_game($game['identifier'], $game['title'], $result->id, $provider, $game_type->game_type_id, $game_slug, $provider_id);

            }

        }

    }		

 

    public function refresh_slots_onlyplay(Request $request)  
    {
        
        $provider = "onlyplay";
		
        $img_url = "";
        $this->prepare_onlyplay("https://int.onlyplay.net/integration/onlyplay/games?provider=onlyplay,justplay", "1");

        $json = $this->getGameList_onlyplay([])->getBody()->getContents();
	dd($json);
        $hash = md5($json);
        $data = json_decode($json);		
	
        $result = $this->check_hash($hash, $provider);
		
        if (isset($result->id)) {
            echo 'hash matches the DB';
            exit;
        }

        foreach ($data as $key => $game) {


			$provider_id = 23;
		
            $result = $this->check_game($provider, $game->bundle);

            if (!isset($result->id)) {

                $game_type = $this->get_game_type("slot");

                if (!isset($game_type->game_type_id)) {
                    print_r($game);
                    echo 'no game type found. halting..';
                    exit;
                }

				if($game->bundle == 'luckypunch') {
					$game->img_icon = 'https://first.ua/static/img/icons/onlyplay/lucky-punch.jpg';
				}
				
                $img_remote_path = $game->img_icon;
                $img_local_path = public_path('uploads/' . $provider . '/' . $game->bundle . '.webp');
                $img_local_save_path = 'uploads/' . $provider . '/' . $game->bundle . '.webp';
				
                $image_type = exif_imagetype($img_remote_path);

                if ($image_type == 3) {
                    $img = imagecreatefrompng($img_remote_path);
                    imagepalettetotruecolor($img);
                    $image = Image::make($img)->encode('webp', 90)->save($img_local_path);
                } else {

                    $image = Image::make($img_remote_path)->encode('webp', 90)->save($img_local_path);

                }

                $game_slug = Str::slug($game->title);

                $this->insert_game($game->bundle, $game->title, $provider, $game_type->game_type_id, $img_local_save_path, $game_slug, $provider_id);

                echo 'game ' . $game->title . ' inserted<br/>';

            } else {

                $game_type = $this->get_game_type("slot");
                $game_slug = Str::slug($game->title);

                $this->update_game($game->bundle, $game->title, $result->id, $provider, $game_type->game_type_id, $game_slug, $provider_id);

            }

        }

    }			
	
    public function refresh_slots_slotty(Request $request)  
    {
        
        $provider = "slotty";

        $img_url = "";
        $this->prepare_slotty("https://site-ire1.mrslotty.com", "d8b788d5-06de-4807-9f01-a03fdd81081c");

        $json = $this->getGameList_slotty([
			'action' => 'available_games',
			'secret' => 'd8b788d5-06de-4807-9f01-a03fdd81081c',
		])->getBody()->getContents();
	
        $hash = md5($json);
        $data = json_decode($json);		
	
        $result = $this->check_hash($hash, $provider);
		
        if (isset($result->id)) {
            echo 'hash matches the DB';
            exit;
        }
		
		
        foreach ($data->response as $key => $game) {

			if($game->provider != 'BGaming') continue;  
			$sub_provider = "slotty_bgaming";
			$provider_id = 39;
		
            $result = $this->check_game($sub_provider, $game->alias);

            if (!isset($result->id)) {

                $game_type = $this->get_game_type("slot");

                if (!isset($game_type->game_type_id)) {
                    print_r($game);
                    echo 'no game type found. halting..';
                    exit;
                }

				$media = (array) $game->media->thumbnails;
				$thefirstpic = array_shift($media);
				
                $img_remote_path = $thefirstpic;
                $img_local_path = public_path('uploads/' . $provider . '/' . $game->alias . '.webp');
                $img_local_save_path = 'uploads/' . $provider . '/' . $game->alias . '.webp';


                $image_type = exif_imagetype($img_remote_path);

                if ($image_type == 3) {
                    $img = imagecreatefrompng($img_remote_path);
                    imagepalettetotruecolor($img);
                    $image = Image::make($img)->encode('webp', 90)->save($img_local_path);
                } else {

                    $image = Image::make($img_remote_path)->encode('webp', 90)->save($img_local_path);

                }

                $game_slug = Str::slug($game->name);

                $this->insert_game($game->alias, $game->name, $sub_provider, $game_type->game_type_id, $img_local_save_path, $game_slug, $provider_id);

                echo 'game ' . $game->name . ' inserted<br/>';

            } else {

                $game_type = $this->get_game_type("slot");
                $game_slug = Str::slug($game->name);

                $this->update_game($game->alias, $game->name, $result->id, $sub_provider, $game_type->game_type_id, $game_slug, $provider_id);

            }

        }

    }		

    public function refresh_slots_igrosoft(Request $request)  
    {
        
        $provider = "igrosoft";

        $img_url = "https://test.math-server.net/";
        $this->prepare_igrosoft("https://test.math-server.net/icasino2/", "072C1CE87F34CC46F7A51D78E2C5E971");

        $json = $this->getGameList_igrosoft([
			'merchant_id' 	 => 'cryptocasinocity.com',
			'transaction_id' => "100000".time(),
			'timestamp'		 => date('Ymd')."T".date('His'),
		])->getBody();
        $hash = md5($json);
        $data = json_decode($json);
		
        $result = $this->check_hash($hash, $provider);

        if (isset($result->id)) {
            echo 'hash matches the DB';
            exit;
        }
        foreach ($data as $key => $game) {

            $result = $this->check_game($provider, $game->name);

            if (!isset($result->id)) {

                $game_type = $this->get_game_type("slot");

                if (!isset($game_type->game_type_id)) {
                    print_r($game);
                    echo 'no game type found. halting..';
                    exit;
                }

                $img_remote_path = $img_url . $game->splash;
                $img_local_path = public_path('uploads/' . $provider . '/' . $game->name . '.webp');
                $img_local_save_path = 'uploads/' . $provider . '/' . $game->name . '.webp';


                $image_type = exif_imagetype($img_remote_path);

                if ($image_type == 3) {
                    $img = imagecreatefrompng($img_remote_path);
                    imagepalettetotruecolor($img);
                    $image = Image::make($img)->encode('webp', 90)->save($img_local_path);
                } else {

                    $image = Image::make($img_remote_path)->encode('webp', 90)->save($img_local_path);

                }

                $game_slug = Str::slug($game->public);

                $this->insert_game($game->name, $game->public, $provider, $game_type->game_type_id, $img_local_save_path, $game_slug);

                echo 'game ' . $game->public . ' inserted<br/>';

            } else {

                $game_type = $this->get_game_type("slot");
                $game_slug = Str::slug($game->public);

                $this->update_game($game->name, $game->public, $result->id, $provider, $game_type->game_type_id, $game_slug);

            }

        }

    }	
	
	
	public function refresh_slots_belatra(Request $request)
	{
		echo 'test';
		exit;
		$provider = "belatra";
		$img_url = "https://test.belatragames.com:34443";
		$this->prepare_belatra("https://test.belatragames.com:34443/api/ss","r6qkg5b676484923");
		
		$json = $this->getGameList([])->getBody();
		$hash =  md5($json);
		$data = json_decode($json);
		
		$result = $this->check_hash($hash, $provider);
		
		if(isset($result->id)) {
			echo 'hash matches the DB';
			exit;
		}
		foreach($data as $key=>$game) {
			
			$result = $this->check_game($provider, $game->nickname);
			
			if(!isset($result->id)) {
				
				$game_type = $this->get_game_type($game->category);
				
				if(!isset($game_type->game_type_id)) {
					print_r($game);
					echo 'no game type found. halting..';
					exit;
				}
				
				$img_remote_path = $img_url.$game->icon[1];	
				$img_local_path = public_path('uploads/' . $provider . '/'  .  $game->nickname . '.webp');
				$img_local_save_path = 'uploads/' . $provider . '/'  .  $game->nickname . '.webp';
				
				$image_type = exif_imagetype($img_remote_path);
				
				if($image_type == 3) {
					$img = imagecreatefrompng($img_remote_path); 
					imagepalettetotruecolor($img);
					$image = Image::make($img)->encode('webp', 90)->save($img_local_path);
				} else {
					
					$image = Image::make($img_remote_path)->encode('webp', 90)->save($img_local_path);
					
				}		
				
				$game_slug =  Str::slug($game->gamename);
				
				$this->insert_game($game->nickname, $game->gamename, $provider, $game_type->game_type_id, $img_local_save_path, $game_slug);
				
				echo 'game '.$game->gamename.' inserted<br/>';
				
			} else {
				
				$game_type = $this->get_game_type($game->category);
				$game_slug =  Str::slug($game->gamename);

				$this->update_game($game->nickname, $game->gamename, $result->id, $provider, $game_type->game_type_id, $game_slug);	
				
			}
			
		}
				
	}

	public function refresh_slots_outcomebet(Request $request)
	{
		
		$provider_map = [
			24 => "greentube",
			25 => "igt",
			26 => "aristocrat",
			27 => "gaminator",
			28 => "quickspin",
			29 => "playtech",
			30 => "microgaming",
			31 => "wazdan",
			32 => "kajot",
			33 => "booongo",
			34 => "playson",
			35 => "konami",
			36 => "fishing",
			37 => "egt",
			38 => "pragmatic",
		];
		
		$provider_skip_map = ["netent", "spadegaming", "austria", "amatic", "igrosoft", "apollo", "merkur", "cqgaming"];
		
		$provider = "outcomebet";
		$provider_id = 5;
		$this->prepareMascotAPI('https://api.c27.games/v1/','../../ssl-outcomebet/apikey.pem');
		$data = $this->gameList();
		
		
		$json = json_encode($data);
		$hash =  md5($json);
		
		$result = $this->check_hash($hash, $provider);
		
		if(isset($result->id)) {
			echo 'hash matches the DB';
			exit;
		}
		
		$data = $data['Games'];	
		
		foreach($data as $key=>$game) {

			if(!in_array($game['SectionId'], $provider_skip_map) && !in_array($game['SectionId'], $provider_map)) {
				echo 'not found '.$game['SectionId'];
				exit;
			}
			
			if(in_array($game['SectionId'], $provider_skip_map)) continue;
			
			$provider = $game['SectionId'];
			$provider_id = array_search($provider, $provider_map);
		
		
			$result = $this->check_game($provider, $game['Id']);
			$game['Type'] = "slot";
			
			if(!isset($result->id)) {
				
				$skip_games = ["fruits_n_stars_c_ps_html", "fruits_n_stars_he_ps_html"];
				
				if(in_array($game["Id"], $skip_games)) continue; 
				
				$game_type = $this->get_game_type($game['Type']);
				
				if(!isset($game_type->game_type_id)) {
					print_r($game);
					echo 'no game type type found. halting..';
					exit;
				}				
				
				$img_remote_path = public_path('tmp/' . $game['Id'] . '.jpg');	
				$img_local_path = public_path('uploads/outcomebet_' . $provider . '/'  .  $game['Id'] . '.webp');
				$img_local_save_path = 'uploads/outcomebet_' . $provider . '/'  .  $game['Id'] . '.webp';				
				
				$image_type = exif_imagetype($img_remote_path);
				
				if($image_type == 3) {
					$img = imagecreatefrompng($img_remote_path); 
					imagepalettetotruecolor($img);
					$image = Image::make($img)->encode('webp', 90)->save($img_local_path);
				} else {
					
					$image = Image::make($img_remote_path)->encode('webp', 90)->save($img_local_path);
					
				}		
				
				$game_slug =  Str::slug($game['Name']);
				
				$this->insert_game($game['Id'], $game['Name'], 'outcomebet_'.$provider, $game_type->game_type_id, $img_local_save_path, $game_slug, $provider_id);
				
				echo 'game '.$game['Name'].' inserted<br/>';
				
			} else {
				
				$game_type = $this->get_game_type($game['Type']);			
				$game_slug =  Str::slug($game['Name']);

				$this->update_game($game['Id'], $game['Name'], $result->id, 'outcomebet_'.$provider, $game_type->game_type_id, $game_slug, $provider_id);			
				
			}
			
		}
		
		exit;
		
	}
		
	
	public function refresh_slots_mascot(Request $request)
	{
		
		$provider = "mascot";
		$provider_id = 5;
		$this->prepareMascotAPI('https://api.mascot.games/v1/','../../ssl/apikey.pem');
		$data = $this->gameList();
		
		$json = json_encode($data);
		$hash =  md5($json);
		
		$result = $this->check_hash($hash, $provider);
		
		if(isset($result->id)) {
			echo 'hash matches the DB';
			exit;
		}
		
		$data = $data['Games'];	
		
		foreach($data as $key=>$game) {

			$result = $this->check_game($provider, $game['Id']);
			$game['Type'] = "slot";
			
			if(!isset($result->id)) {
				
				$game_type = $this->get_game_type($game['Type']);
				
				if(!isset($game_type->game_type_id)) {
					print_r($game);
					echo 'no game type type found. halting..';
					exit;
				}				
				
				$img_remote_path = public_path('tmp/' . $game['Id'] . '.png');	
				$img_local_path = public_path('uploads/' . $provider . '/'  .  $game['Id'] . '.webp');
				$img_local_save_path = 'uploads/' . $provider . '/'  .  $game['Id'] . '.webp';				
				
				$image_type = exif_imagetype($img_remote_path);
				
				if($image_type == 3) {
					$img = imagecreatefrompng($img_remote_path); 
					imagepalettetotruecolor($img);
					$image = Image::make($img)->encode('webp', 90)->save($img_local_path);
				} else {
					
					$image = Image::make($img_remote_path)->encode('webp', 90)->save($img_local_path);
					
				}		
				
				$game_slug =  Str::slug($game['Name']);
				
				$this->insert_game($game['Id'], $game['Name'], $provider, $game_type->game_type_id, $img_local_save_path, $game_slug, $provider_id);
				
				echo 'game '.$game['Name'].' inserted<br/>';
				
			} else {
				
				$game_type = $this->get_game_type($game['Type']);			
				$game_slug =  Str::slug($game['Name']);

				$this->update_game($game['Id'], $game['Name'], $result->id, $provider, $game_type->game_type_id, $game_slug, $provider_id);			
				
			}
			
		}
		
		exit;
		
	}
	
	
	public function refresh_slots_booming(Request $request)
	{
		
		$provider = "booming";
		$img_url = "https://games.eu.booming-games.com/";
		
		$this->prepare_booming('https://api.intgr.booming-games.com/', '222222'.time(), 'TTO+X7qkfa5/qH0bbEnH075J2+2P/Q9TpoNT3YhRYGLfEDfPAEcXn+vJU41jAkGP', 'DblvoWy/RwEL7xl2PyADUg==');
		$data = $this->booming_listOfGames([]);
	
		$json = json_encode($data);
		$hash =  md5($json);
		
		$result = $this->check_hash($hash, $provider);
		
		if(isset($result->id)) {
			echo 'hash matches the DB';
			exit;
		}
		
		$counter = 0;		
		foreach($data as $key=>$game) {

			$result = $this->check_game($provider, $game->game_name);
			
			if(!isset($result->id)) {
				
				$game_type = $this->get_game_type('slot');
				
				if(!isset($game_type->game_type_id)) {
					print_r($game);
					echo 'no game type type found. halting..';
					exit;
				}
				
				$img_remote_path = $img_url.$game->logo_url;		
				$img_local_path = public_path('uploads/' . $provider . '/'  .  $game->game_name . '.webp');
				$img_local_save_path = 'uploads/' . $provider . '/'  .  $game->game_name . '.webp';
				
				if(file_exists($img_remote_path)) {			
					$image_type = exif_imagetype($img_remote_path);			
				} else {
					continue;
				}				
				
				
				if($image_type == 3) {
					$img = imagecreatefrompng($img_remote_path); 
					imagepalettetotruecolor($img);
					$image = Image::make($img)->encode('webp', 90)->save($img_local_path);
				} else {
					
					$image = Image::make($img_remote_path)->encode('webp', 90)->save($img_local_path);
					
				}		
				$game_slug =  Str::slug($game->title_name);
				
				$this->insert_game($game->game_id, $game->title_name, $provider, $game_type->game_type_id, $img_local_save_path, $game_slug);
				
				echo 'game '.$game->title_name.' inserted<br/>';
				$counter = $counter + 1;
				if($counter == 20) exit;
				
			} else {
				
				$game_type = $this->get_game_type('slot');			
				$game_slug =  Str::slug($game->title_name);

				$this->update_game($game->game_id, $game->title_name, $result->id, $provider, $game_type->game_type_id, $game_slug);			
				
			}
			
		}
		
		exit;
		
	}


	public function refresh_slots_evo(Request $request)
	{

		$provider = "evo";
		$provider_id = 1;
		//$img_url = "https://games.eu.booming-games.com/";
		
		$this->prepare_evoplay('http://api.evoplay.games', 3873, 1, 'eb9d2e8163a8ce4637f3aa75fd46c1ce');
		$data = $this->evo_getListGames([]);
		
		$json = json_encode($data);
		$hash =  md5($json);

		$result = $this->check_hash($hash, $provider);

		if(isset($result->id)) {
			echo 'hash matches the DB';
			exit;
		}
		
		$counter = 0;		
		foreach($data->data as $key=>$game) {

			$result = $this->check_game($provider, $key);
			
			if(!isset($result->id)) {
				
				$game_type = $this->get_game_type('slot');
				
				if(!isset($game_type->game_type_id)) {
					print_r($game);
					echo 'no game type type found. halting..';
					exit;
				}
				
				$game_slug =  Str::slug($game->name);
				
				$img_remote_path = public_path('tmp/' . $key . '.jpg');		
				$img_local_path = public_path('uploads/' . $provider . '/'  .  $game_slug . '.webp');
				$img_local_save_path = 'uploads/' . $provider . '/'  .  $game_slug . '.webp';
				
				/* 
				if(file_exists($img_remote_path)) {			
					$image_type = exif_imagetype($img_remote_path);			
				} else {
					continue;
				}				
				
				if($image_type == 3) {
					$img = imagecreatefrompng($img_remote_path); 
					imagepalettetotruecolor($img);
					$image = Image::make($img)->encode('webp', 90)->save($img_local_path);
				} else {
					
					$image = Image::make($img_remote_path)->encode('webp', 90)->save($img_local_path);
					
				} */	
								
				$this->insert_game($key, $game->name, $provider, $game_type->game_type_id, $img_local_save_path, $game_slug, $provider_id);
				
				echo 'game '.$game->name.' inserted<br/>';
				$counter = $counter + 1;
				if($counter == 50) exit;
				
			} else {
				
				$game_type = $this->get_game_type('slot');			
				$game_slug =  Str::slug($game->name);

				$this->update_game($key, $game->name, $result->id, $provider, $game_type->game_type_id, $game_slug, $provider_id);			
				
			}
			
		}
		
		exit;
		
	}

	public function refresh_slots_relax(Request $request)
	{
		

		$provider = "relax";
		
		$this->prepare_relax('https://stag-casino-partner.api.relaxg.net:7000', 'housecasino', 'OIYEAFkiY2VAq8Zm');
		$data = $this->relax_get_available_games([
			"jurisdiction" => "CW",
		]);
		
		$json = json_encode($data);
		$hash =  md5($json);
		
		$result = $this->check_hash($hash, $provider);

		if(isset($result->id)) {
			echo 'hash matches the DB';
			exit;
		}
		
		$counter = 0;		
		foreach($data->games as $key=>$game) {
		
			$result = $this->check_game($provider, $game->gameid);
			
			if(!isset($result->id)) {
				
				$game_type = $this->get_game_type('slot');
				
				if(!isset($game_type->game_type_id)) {
					print_r($game);
					echo 'no game type type found. halting..';
					exit;
				}
				
				$game_slug =  Str::slug($game->name);
				
				$img_remote_path = public_path('tmp/' . $game->gameid . '.png');		
				$img_local_path = public_path('uploads/' . $provider . '/'  .  $game_slug . '.webp');
				$img_local_save_path = 'uploads/' . $provider . '/'  .  $game_slug . '.webp';
				
		
				if(file_exists($img_remote_path)) {			
					$image_type = exif_imagetype($img_remote_path);			
				} else {
					continue;
				}			
				
				if($image_type == 3) {
					$img = imagecreatefrompng($img_remote_path); 
					imagepalettetotruecolor($img);
					$image = Image::make($img)->encode('webp', 90)->save($img_local_path);
				} else {
					
					$image = Image::make($img_remote_path)->encode('webp', 90)->save($img_local_path);
					
				}	
					
				$this->insert_game($game->gameid, $game->name, $provider, $game_type->game_type_id, $img_local_save_path, $game_slug);
					
				echo 'game '.$game->name.' inserted<br/>';		

				$counter = $counter + 1;
				if($counter == 20) exit;
				
			} else {
				
				$game_type = $this->get_game_type('slot');			
				$game_slug =  Str::slug($game->name);
				
				$this->update_game($game->gameid, $game->name, $result->id, $provider, $game_type->game_type_id, $game_slug);			
				
			}
			
		}
		
		exit;
		
	}

	public function refresh_slots_zillion(Request $request)
	{
		
		$provider = "zillion";
		//$img_url = "https://test.belatragames.com:34443";
		$this->prepare_belatra("https://back.stage.zilliongames.io","ahsgfda6756231hgas7652");
		
		$json = $this->getGameList_zillion([])->getBody();
		$hash =  md5($json);
		$data = json_decode($json);
		
		$result = $this->check_hash($hash, $provider);
		
		if(isset($result->id)) {
			echo 'hash matches the DB';
			exit;
		}
		foreach($data->games as $key=>$game) {
			
			$result = $this->check_game($provider, $game->identifier);
			
			if(!isset($result->id)) {
				
				$game_type = $this->get_game_type("slot");
				
				if(!isset($game_type->game_type_id)) {
					print_r($game);
					echo 'no game type found. halting..';
					exit;
				}
				
				$img_remote_path = public_path('tmp/' . $game->identifier . '.png');	
				$img_local_path = public_path('uploads/' . $provider . '/'  .  $game->identifier . '.webp');
				$img_local_save_path = 'uploads/' . $provider . '/'  .  $game->identifier . '.webp';
				
				$image_type = exif_imagetype($img_remote_path);
				
				if($image_type == 3) {
					$img = imagecreatefrompng($img_remote_path); 
					imagepalettetotruecolor($img);
					$image = Image::make($img)->encode('webp', 90)->save($img_local_path);
				} else {
					
					$image = Image::make($img_remote_path)->encode('webp', 90)->save($img_local_path);
					
				}	
				
				$game_slug =  Str::slug($game->title);
				
				$this->insert_game($game->identifier, $game->title, $provider, $game_type->game_type_id, $img_local_save_path, $game_slug);
				
				echo 'game '.$game->title.' inserted<br/>';	
				
			} else {
				
				$game_type = $this->get_game_type($game->category);
				$game_slug =  Str::slug($game->title);

				$this->update_game($game->identifier, $game->title, $result->id, $provider, $game_type->game_type_id, $game_slug);	
				
			}
			
		}
			
		
	}	

	public function refresh_slots_spinomenal(Request $request)
	{
		

		$provider = "spinomenal";
		
		$this->prepare_spinomenal('https://api-test.spinomenal.com');
		
		$data = $this->get_games_spinomenal(['partnerId' => 'housecasino-dev']);
		
		$json = json_encode($data);
		$hash =  md5($json);
		
		$result = $this->check_hash($hash, $provider);

		if(isset($result->id)) {
			echo 'hash matches the DB';
			exit;
		}
		
		$counter = 0;		
		
		foreach($data->Data as $key=>$game) {
			
			$result = $this->check_game($provider, $game->GameCode);
			
			if(!isset($result->id)) {
				
				$game_type = $this->get_game_type('slot');
				
				if(!isset($game_type->game_type_id)) {
					print_r($game);
					echo 'no game type type found. halting..';
					exit;
				}
				
				$game_slug =  Str::slug($game->GameName);
				
				$img_remote_path = public_path('tmp/' . $game->GameCode . '.jpg');		
				$img_local_path = public_path('uploads/' . $provider . '/'  .  $game_slug . '.webp');
				$img_local_save_path = 'uploads/' . $provider . '/'  .  $game_slug . '.webp';
				
		
				if(file_exists($img_remote_path)) {			
					$image_type = exif_imagetype($img_remote_path);			
				} else {
					continue;
				}			
				
				if($image_type == 3) {
					$img = imagecreatefrompng($img_remote_path); 
					imagepalettetotruecolor($img);
					$image = Image::make($img)->encode('webp', 90)->save($img_local_path);
				} else {
					
					$image = Image::make($img_remote_path)->encode('webp', 90)->save($img_local_path);
					
				}	
								
					
				$this->insert_game($game->GameCode, $game->GameName, $provider, $game_type->game_type_id, $img_local_save_path, $game_slug);
					
				echo 'game '.$game->GameName.' inserted<br/>';		

				$counter = $counter + 1;
				if($counter == 20) exit;
				
			} else {
				
				$game_type = $this->get_game_type('slot');			
				$game_slug =  Str::slug($game->GameName);
				
				$this->update_game($game->GameCode, $game->GameName, $result->id, $provider, $game_type->game_type_id, $game_slug);			
				
			}
			
		}
		
		exit;
		
	}
	
	public function update_consents()
	{
		
		$this->client = $this->initiateRestAPI();
		
		$consents = $this->get_all_consents("en");
		$statics = $this->get_all_static();
		
		foreach($consents as $consent) {
			
			foreach($statics as $static) {
				
				if($static->igc_slug == $consent->Name) {
					
					$this->update_consent($static->id, $consent->ConsentContents[0]->Content);
					
				}				
				
			}			
			
		}
		
	}
	
	public function import_translations____() 
	{
		
		$file = fopen($_SERVER['DOCUMENT_ROOT'].'../storage/app/public/data.csv', "r");
		$all_data = array();
		
		while (($data = fgetcsv($file, 400, ",")) !== false) {

			if($data[2] == "Message") continue;
		
			$input = [
				"path" => "Global/Errors",
				"language" => "en",
				"code" => "Error_".$data[1],
				"translation" => $data[2],
			];
			
			
			//$response = $this->client->post('Translations/AddTranslation', ['json' => $input])->getBody()->getContents();
						
		}
		
		fclose($file);
		
	}
	
	public function import_translations() 
	{
		
		//remove old translations
		//this should be your first step when trying to import data to iGC
		$this->client = $this->initiateRestAPI();
		
		$file = fopen($_SERVER['DOCUMENT_ROOT'].'../storage/app/public/16.11.2018.csv', "r");
		$all_data = array();
		$key=0; 
		$big_array = [];
		
		while (($data = fgetcsv($file, 10000, ",")) !== false) {

			$key++;
		
			$data = array_filter($data);


			
			//if($data[2] == "Message") continue;
			if($key<2) continue;
			//if($key==3) break;

			//$input = [
			//	"path" => "Global/Errors",
			//	"language" => "en",
			//	"code" => "Error_".$data[1],
			//	"translation" => $data[2],
			//];
			
			if(!isset($data[2]) || !isset($data[4]) || !isset($data[8])) {
				//pre($data);
				continue;
			}
			
			if(!isset($data[10])) {
				echo 'alarm!';
				pre($data);
				exit;
			}
			
			$big_input = [
				[
					"path" => $data[2],
					"Alpha2Code" => "ca",
					"code" => $data[0],
					"translation" => $data[10],
				],
				[
					"path" => $data[2],
					"Alpha2Code" => "uk",
					"code" => $data[0],
					"translation" => $data[9],
				],
				[
					"path" => $data[2],
					"Alpha2Code" => "de",
					"code" => $data[0],
					"translation" => $data[4],
				],
				[
					"path" => $data[2],
					"Alpha2Code" => "fi",
					"code" => $data[0],
					"translation" => $data[5],
				],
				[
					"path" => $data[2],
					"Alpha2Code" => "no",
					"code" => $data[0],
					"translation" => $data[6],
				],
				[
					"path" => $data[2],
					"Alpha2Code" => "sv",
					"code" => $data[0],
					"translation" => $data[7],
				],
				[
					"path" => $data[2],
					"Alpha2Code" => "fr",
					"code" => $data[0],
					"translation" => $data[8],
				],
			];
			
			//foreach($big_input as $input) {	
			//}
			
			$big_array = array_merge($big_array, $big_input);
			
		}
		//pre($big_array);
		//exit;
		//pre($big_array);	
		//$chunks = array_chunk($big_array, 100);
		//pre($chunks);
		
		foreach($big_array as $key=>$value) {
			$this->insert_translation($value);
		}
		
		//$response = $this->client->post('Translations/add', ['json' => $chunks[0]])->getBody()->getContents();		
		//pre($response);
		 
		fclose($file);
		
	}

	public function update_translations()
	{
		
		//first u are in need to fill database with values from import_translations()
		//second step when importing values to iGC
		$this->client = $this->initiateRestAPI(["X-Api-Key" => "ee05b910-cfae-40fb-ab46-c2c160a132be"]);
		
		$dataset = $this->get_a_translation();
		
		//pre($dataset);
		//exit;
		foreach($dataset as $one) {
			
			if(strpos($one->translation, "<") !== false) {
				//echo 'found';
				//$this->set_processed_two($one->id);
				//continue;
			}
			
			$search = ["language" => $one->Alpha2Code, "showUpdatedAlso" => true, "search" => $one->code];
			
			$response = $this->client->post('Translations/v2/GetAllTranslations', ['json' => $search])->getBody()->getContents();	
			$response = json_decode($response);
						
			if($response->Data) {
				
				echo 'we searched for:';
				echo $search["search"].'<br/>';
				echo 'we found this:';
				pre($response->Data);				
							
				$translation_id = $response->Data[0]->TranslationID;
				
				if(strpos($one->translation,"javascript") !== false || strpos($one->translation,"popup-link") !== false ) $this->set_processed($one->id);
				
				$found = 0;
				
				if(count($response->Data) > 1) {
					
					echo 'we have multiples<br/>';
					
					foreach($response->Data as $value) {
						if($value->Code == $one->code) {
							$translation_id = $value->TranslationID;
							echo '<br/>we chose:';
							pre($value);
							$found = 1;
						}
					}
					
				} else {
					
					echo 'there is just one<br/>';
					
					if($response->Data[0]->Code == $one->code) {
						
						$found = 1;
					
					}
				}
				
				if (!$found) {
					
					echo 'nothing found!<br/>';
					echo 'going to insert<br/>';
					
					
					$insert = ["path" => $one->path, "language" => $one->Alpha2Code, "code" => $one->code, "translation" => $one->translation];				
					$response = $this->client->post('Translations/v2/AddTranslation', ['json' => $insert])->getBody()->getContents();
					pre($response);						
					continue;
					
				}
				
				echo 'going to update<br/>';
				
				$update = ["translationID" => $translation_id, "userId" => 1, "translation" => $one->translation];				
				$response = $this->client->post('Translations/v2/UpdateTranslation', ['json' => $update])->getBody()->getContents();
				pre($response);
				
			} else {
				
				echo 'going to insert<br/>';						
				
				$insert = ["path" => $one->path, "language" => $one->Alpha2Code, "code" => $one->code, "translation" => $one->translation];	
				
				pre($insert);
				
				$response = $this->client->post('Translations/v2/AddTranslation', ['json' => $insert])->getBody()->getContents();
				pre($response);

				
			}
			
			$this->set_processed($one->id);
			
		}
		
		pre($one);
		
	}	
	
	public function export_translations()
	{
		
		//from iGC to database, several calls
		$this->client = $this->initiateRestAPI();
		$language_array = $this->get_db_languages();
		
		foreach($language_array as $language) {
			
			$count = $this->count_translations($language->language_code);
			
			if($count > 0) {
				
				continue;
				
			} else {
				
				$search = ["language" => $language->language_code, "showUpdatedAlso" => true];
				$response = $this->client->post('Translations/GetAllTranslations', ['json' => $search])->getBody()->getContents();			
				$response = json_decode($response);
				
				foreach($response->Data as $value) {
					
					if(strtotime($value->Created) < 1498508781) continue;
					
					$data = [
						'path' 			 => $value->Path,
						'code' 			 => $value->Code,
						'translation' 	 => $value->Translation,
						'Alpha2Code' 	 => $value->Language,
						'created_at'     => strtotime($value->Created),
						'translation_id' => 0,
					];
					
					$this->insert_translation($data);
					
				}
				
				break;
				
			}
			
		}
		
		
	}
	
	public function create_translations()
	{
		//from database to file
		$this->client = $this->initiateRestAPI();
		
		$fp = fopen($_SERVER['DOCUMENT_ROOT'].'../storage/app/public/forT'.date("d-m-Y").'.csv', 'w');

		$translations = $this->get_translations();
		
		$to_create = [];
		foreach($translations as $translation) {
			
			if($translation->Alpha2Code != "en") continue;
			if(strpos($translation->code,"_") === false) continue;
					
			$starter = [
				$translation->code,
				"English",
				$translation->path,
				$translation->translation,
			];
			
			$other = $this->get_translation_by_code($translation->code);
			
			foreach($other as $one) {
				
				switch($one->Alpha2Code) {
				case "de":
					$starter[4] = $one->translation;
				break;
				case "fi":
					$starter[5] = $one->translation;
				break;
				case "no":
					$starter[6] = $one->translation;
				break;
				case "sv":
					$starter[7] = $one->translation;
				break;
				case "fr":
					$starter[8] = $one->translation;
				break;
				case "uk":
					$starter[9] = $one->translation;
				break;
				case "ca":
					$starter[10] = $one->translation;
				break;
				}
				
			}
			
			ksort($starter);
			
			$to_create[] = $starter;
			
			
		}
		
		pre($to_create);
		
		foreach ($to_create as $fields) {
			fputcsv($fp, $fields);
		}

		fclose($fp);		
		
	}
	

	public function refresh_total_temp() 
	{
		
		$fg = $this->get_allowed_games();
		$fg = $this->make_clear_listing($fg, "game_id");
		$langs = ["en","fi","no","de","sv","fr","uk","ca"];
		$devices = [1,2,3,4,5,6,7,8];
		$lang = [];
		foreach($langs as $lang) {
			foreach($devices as $value) {
				$this->refresh_specific_temp($value, $lang, $fg);
			}
		}
		
	}	
	
	public function refresh_games(Request $request)
	{
		
		$input = $request->all();
		
		if(!isset($input['lang'])) {
			mail('magistriam@gmail.com','no lang','no unfortunetaly');
			return;
		}
		$devices = [1,2,3,4,5,6,7,8];
		
		$fg = $this->get_allowed_games();
		$fg = $this->make_clear_listing($fg, "game_id");

		foreach($devices as $value) {
			$this->refresh_specific_temp($value, $input['lang'], $fg);
		}
		
	}
	
	private function create_siblings($game,$category) {
		
		$new_game = clone($game);
		
		$new_game->Categories = [$category];
		$new_game->OrderNumber = $category->OrderNumber;
		//pre($new_game);
		return $new_game;
		
	}

	public function refresh_specific_temp($device, $lang, $fg) 
	{
		
		
		$this->client = $this->initiateRestAPI();
		
		$games = $this->get_games(array('language'=>$lang,"Device"=>$device));
		
		for($i=0;$i<count($games);$i++) {
			if(count($games[$i]->Categories) > 1) {
				foreach($games[$i]->Categories as $key=>$value) {
					//if(!$key) continue;
					$games[] = $this->create_siblings($games[$i],$games[$i]->Categories[$key]);
				}
				unset($games[$i]);
			}
		}
		
		
		usort($games, function ($a, $b) {
			return $a->OrderNumber - $b->OrderNumber;
		});
		
		
		//if($lang == "en" && $device == 1) {
		//	pre($games);
		//	echo 'hi';
		//	exit; 
		//}
		foreach($games as $key=>$game) {
			
				
			$game_url = str_replace(" ","_",$game->Name);
			$post = Corcel\Model\Post::published()->hasMeta('game_id', $game->GameId)->first();
			if(!$post) {
				//echo 'creating';
				$result = Corcel\Model\Post::addPost($game->Name);
				Corcel\Model\TermRelationship::addPostRelationship($result->ID, 41);
				Corcel\Model\Meta\PostMeta::addPostMeta($result->ID,"game_id",$game->GameId);
				Corcel\Model\Meta\PostMeta::addPostMeta($result->ID,"game_url",$game_url);
			} else {
				//echo 'created';
			}				
				
				
			
			$games[$key]->Name = str_replace("’","",$game->Name);
			$games[$key]->Name = substr($game->Name,0,20);
		}
		
		$uk_games = [];
		$i=0; 
		foreach($games as $key=>$game) {
			if(in_array($game->GameId, $fg)) {
				$uk_games[$i] = $game;
				$uk_games[$i]->Name = str_replace("’","",$game->Name);
				$uk_games[$i]->Name = substr($game->Name,0,20);
				$i++;
			} else {
				//echo $game->Name.'<br/>';
			}
		}		
		 
		
		$games = json_encode($games);
		$uk_games = json_encode($uk_games); 
		
		if($lang != "uk") { 
			$file = fopen($_SERVER['DOCUMENT_ROOT'].'../storage/app/public/games-'.$lang.'-'.$device.'.json', "w");
			fwrite($file, $games);
			fclose($file);
		} else {
			
			$file = fopen($_SERVER['DOCUMENT_ROOT'].'../storage/app/public/games-'.$lang.'-'.$device.'.json', "w");
			fwrite($file, $games);
			fclose($file);
			
			$file = fopen($_SERVER['DOCUMENT_ROOT'].'../storage/app/public/games-'.$lang.'-nonauth-'.$device.'.json', "w");
			fwrite($file, $uk_games);
			fclose($file);
			
		}
		
		//$file = fopen($_SERVER['DOCUMENT_ROOT'].'../storage/app/public/games-'.$lang.'-uk-'.$device.'.json', "w");
		//fwrite($file, $uk_games);
		//fclose($file);
		
	}	

	public function refresh_total() 
	{
		
		$fg = $this->get_allowed_games();
		$fg = $this->make_clear_listing($fg, "game_id");
		$langs = ["en","fi","no","de","sv","fr","uk","ca"];
		$devices = [1,2,3,4,5,6,7,8];
		$lang = [];
		foreach($langs as $lang) {
			foreach($devices as $value) {
				$this->refresh_specific($value, $lang, $fg);
			}
		}
		
	}
	
	public function refresh_specific($device, $lang, $fg) 
	{
		
		$this->client = $this->initiateRestAPI();
		
		$games = $this->get_games(array('language'=>$lang,"Device"=>$device));
		
		usort($games, function ($a, $b) {
			return $a->OrderNumber - $b->OrderNumber;
		});
		
		//pre($games);
		
		foreach($games as $key=>$game) {
			$games[$key]->Name = str_replace("’","",$game->Name);
			$games[$key]->Name = substr($game->Name,0,20);
		}
		
		$uk_games = [];
		$i=0; 
		foreach($games as $key=>$game) {
			if(in_array($game->GameId, $fg)) {
				$uk_games[$i] = $game;
				$uk_games[$i]->Name = str_replace("’","",$game->Name);
				$uk_games[$i]->Name = substr($game->Name,0,20);
				$i++;
			} else {
				//echo $game->Name.'<br/>';
			}
		}		
		
		$games = json_encode($games);
		$uk_games = json_encode($uk_games);  
		
		if($lang != "uk") { 
			$file = fopen($_SERVER['DOCUMENT_ROOT'].'../storage/app/public/games-'.$lang.'-'.$device.'.json', "w");
			fwrite($file, $games);
			fclose($file);
		} else {
			
			$file = fopen($_SERVER['DOCUMENT_ROOT'].'../storage/app/public/games-'.$lang.'-'.$device.'.json', "w");
			fwrite($file, $games);
			fclose($file);
			
			$file = fopen($_SERVER['DOCUMENT_ROOT'].'../storage/app/public/games-'.$lang.'-nonauth-'.$device.'.json', "w");
			fwrite($file, $uk_games);
			fclose($file);
			
		}
		
		//$file = fopen($_SERVER['DOCUMENT_ROOT'].'../storage/app/public/games-'.$lang.'-uk-'.$device.'.json', "w");
		//fwrite($file, $uk_games);
		//fclose($file);
		
	}	
    
	public function import_games() 
	{
		
		$this->client = $this->initiateRestAPI();
		
		$games = $this->get_games(array('language'=>"en"));
		
		pre($games);
		exit;
		
		foreach($games as $key=>$game) {
			$games[$key]->Name = str_replace("’","",$game->Name);
			$games[$key]->Name = substr($game->Name,0,20);
		}
		
		$games = json_encode($games);
		
		$file = fopen($_SERVER['DOCUMENT_ROOT'].'../storage/app/public/games-en.json', "w");
		
		fwrite($file, $games);
		
		fclose($file);
		
	}
    
	public function import_uk_games() 
	{
		
		$fg = $this->get_allowed_games();
		$fg = $this->make_clear_listing($fg, "game_id");
		
		$this->client = $this->initiateRestAPI();
		
		$games = $this->get_games(array('Language'=>"en"));

		$uk_games = [];
		$i=0;
		foreach($games as $key=>$game) {
			if(in_array($game->GameId, $fg)) {
				$uk_games[$i] = $game;
				$uk_games[$i]->Name = str_replace("’","",$game->Name);
				$uk_games[$i]->Name = substr($game->Name,0,20);
				$i++;
			} else {
				//echo $game->Name.'<br/>';
			}
		}
		//pre($uk_games);
		//exit;
		$uk_games = json_encode($uk_games);
		pre($uk_games);
		$file = fopen($_SERVER['DOCUMENT_ROOT'].'../storage/app/public/games-en-uk.json', "w");
		
		fwrite($file, $uk_games);
		
		fclose($file);
		
	}
    
	public function import_uk_games_old() 
	{
		
		$fg = $this->get_forbidden_games();
		$fg = $this->make_clear_listing($fg, "game_name");
		
		$this->client = $this->initiateRestAPI();
		
		$games = $this->get_games(array('Language'=>"en"));
		pre($games);
		$uk_games = [];
		foreach($games as $key=>$game) {
			if(!in_array($game->Name, $fg)) {
				$uk_games[$key] = $game;
				$uk_games[$key]->Name = str_replace("’","",$game->Name);
				$uk_games[$key]->Name = substr($game->Name,0,20);
			} else {
				//echo $game->Name.'<br/>';
			}
		}
				
		$uk_games = json_encode($uk_games);
		
		$file = fopen($_SERVER['DOCUMENT_ROOT'].'../storage/app/public/games-en-uk.json', "w");
		
		fwrite($file, $uk_games);
		
		fclose($file);
		
	}
	
	public function check_csv()
	{
		//$row = 0;	
		$this->client = $this->initiateRestAPI();
		
		$csv_games = [];
		if (($handle = fopen($_SERVER['DOCUMENT_ROOT'].'../storage/app/public/gamelist.csv', "r")) !== FALSE) {
		    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
				$num = count($data);
				if(isset($data[0]) && $data[0] && $num && $data[0] > 0) {
					//echo "<p> $num fields in line $row: <br /></p>\n";
					//$row++;
					$key = $data[0];
					$csv_games[$key] = $data[1];
					//echo $data[0].'-'.$data[1].'<br/>';
				}
		    }
		    fclose($handle);
		}
		
		$start = 2400;	
		$limit = 500 + $start;
		$counter = -1;
		foreach($csv_games as $key=>$value) {
			
			$counter++;
			if($counter < $start) continue;
			
			$game = $this->get_game_by_id($key);
			
			if(is_null($game)) {
				echo "------".$key.' is null '.$value.'<br/>';
				continue;
			}
	
			if($value == $game->Name) {
				echo $value.' equals '.$game->Name;
			} else {
				echo "+++++".$value.' not equals '.$game->Name.'--'.$game->GameId;
			}
			echo '<br/>';
			
			if($counter >= $limit) break;
			
		}
		
		//pre($csv_games);
		
	}
	
}
