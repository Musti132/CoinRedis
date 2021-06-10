<?php
namespace CoinRedis;

use CoinRedis\ClusterArray;
use DateTime;

class Cluster{

    private ClusterArray $cluster;

    public function __construct(ClusterArray $clients){
        $this->cluster = $clients;
    }

    public function massWrite(string $data){
        foreach($this->cluster->getClients() as $client){
            $client->write($data);
        }
    }

    public function massSet(string $key, mixed $value, DateTime $ttl){
        foreach($this->cluster->getClients() as $client){
            $client->set($key, $value, $ttl);
        }
    }
}
