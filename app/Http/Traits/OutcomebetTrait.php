<?php
namespace App\Http\Traits;

use JsonRPC\HttpClient;
use JsonRPC\Client as RPCClient;

trait OutcomebetTrait {
	
    public function prepareOutcomebetAPI(string $uri, string $certificate)
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

    public function outcomebet_gameList()
    {
        return $this->outcomebet_execute('Game.List');
    }
    
    public function outcomebet_bankGroupSet(array $params)
    {
        return $this->outcomebet_execute('BankGroup.Set', $params);
    }

    public function outcomebet_playerSet(array $params)
    {
        return $this->outcomebet_execute('Player.Set', $params);
    }

    public function outcomebet_sessionCreate(array $params)
    {
        return $this->outcomebet_execute('Session.Create', $params);
    }
    
    public function outcomebet_sessionCreateDemo(array $params)
    {
        return $this->outcomebet_execute('Session.CreateDemo', $params);
    }
    
    public function outcomebet_sessionClose(array $params): \JsonRPC\Client
    {
        return $this->outcomebet_execute('Session.Close', $params);
    }
    
    public function outcomebet_bonusSet(array $params): \JsonRPC\Client
    {
        return $this->outcomebet_execute('Bonus.Set', $params);
    }
    
    public function outcomebet_bonusList(): \JsonRPC\Client
    {
        return $this->outcomebet_execute('Bonus.List');
    }

    private function outcomebet_getClient(): \JsonRPC\Client
    {
        return $this->client;
    }

    private function outcomebet_execute(string $method, array $params = [])
    {
		
        return $this->getClient()->execute($method, $params);
    }
	
	
}