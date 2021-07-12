<?php

namespace CoinRedis;

use CoinRedis\ClusterArray;
use DateTime;

class Cluster extends ClusterArray
{

    public function __construct(array $clients)
    {
        parent::__construct($clients);
        $this->clients = $this->clients;
    }

    public function massWrite(string $data)
    {
        foreach ($this->getClients() as $client) {
            $client->write($data);
        }
    }

    public function massSet(string $key, $value, DateTime $ttl)
    {
        foreach ($this->getClients() as $client) {
            $client->set($key, $value, $ttl);
        }
    }
}
