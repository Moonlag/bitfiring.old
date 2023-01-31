<?php
namespace App\Http\Traits;

use JsonRPC\HttpClient;
use JsonRPC\Client as RPCClient;

trait MascotTrait {
	
    public function prepareMascotAPI(string $uri, string $certificate)
    {
        if (empty($uri)) {
            throw new GamingException('No URI API detected');
        }

        $http = new HttpClient($uri);

        if (empty($certificate)) {
            throw new GamingException('No certificate detected');
        }
		
		$http->withDebug();
		$http->withHeaders([]);
        $http->withSslLocalCert($certificate);

        $this->client = new RPCClient(null, false, $http);
    }

    public function gameList()
    {
        return $this->mascot_execute('Game.List');
    }
    
    public function bankGroupSet(array $params)
    {
        return $this->mascot_execute('BankGroup.Set', $params);
    }

    public function playerSet(array $params)
    {
        return $this->mascot_execute('Player.Set', $params);
    }

    public function sessionCreate(array $params)
    {
        return $this->mascot_execute('Session.Create', $params);
    }
    
    public function sessionCreateDemo(array $params)
    {
        return $this->mascot_execute('Session.CreateDemo', $params);
    }
    
    public function sessionClose(array $params): \JsonRPC\Client
    {
        return $this->mascot_execute('Session.Close', $params);
    }
    
    public function bonusSet(array $params): \JsonRPC\Client
    {
        return $this->mascot_execute('Bonus.Set', $params);
    }
    
    public function bonusList(): \JsonRPC\Client
    {
        return $this->mascot_execute('Bonus.List');
    }

    private function getClient(): \JsonRPC\Client
    {
        return $this->client;
    }

    private function mascot_execute(string $method, array $params = [])
    {
		
        return $this->getClient()->execute($method, $params);
    }
	
	
}