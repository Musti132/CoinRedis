<?php
namespace CoinRedis;

use CoinRedis\ClusterArray;

class Cluster{

    private CLusterArray $cluster;

    public function __construct(ClusterArray $clients){
        $this->cluster = $clients;
    }

    public function massWrite(string $data){
        foreach($this->cluster->getClients() as $client){
            $client->write($data);
        }
    }
}
