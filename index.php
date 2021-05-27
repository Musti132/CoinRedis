<?php
require __DIR__.'/vendor/autoload.php';

use CoinRedis\Client;
use CoinRedis\ClusterArray;
use CoinRedis\Cluster;

$clusterArray = new ClusterArray([
    new Client("127.0.0.1", 6379),
    new Client("127.0.0.1", 6379),
    new Client("127.0.0.1", 6379),
]);

$clusterArray->add("127.0.0.1", 6379);

$cluster = new Cluster($clusterArray);

$cluster->massWrite("test");

$client = new Client("127.0.0.1", 6379);

$client->write("GET title");
