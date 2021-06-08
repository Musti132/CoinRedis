<?php

namespace CoinRedis;

use CoinRedis\Client;
use CoinRedis\Exceptions\InvalidType;
use Illuminate\Support\Collection;

class ClusterArray
{
    public array $clients;
    public Collection $hosts;

    public function __construct(array $clients)
    {
        $this->clients = $clients;
        $this->hosts = collect();

        foreach ($clients as $client) {
            if (!$client instanceof Client) {
                throw new InvalidType("Clients has to be a instance of CoinRedis\Client class");
            }

            $this->hosts->add($client->host());
        }
        
    }

    public function add(string $ip, int $port){
        $client = new Client($ip, $port);

        $this->clients[] = $client;

        $this->hosts->add($client->host());
        
        return $this;
    }

    public function getHosts()
    {
        return $this->hosts;
    }

    public function getClients()
    {
        return $this->clients;
    }
}
