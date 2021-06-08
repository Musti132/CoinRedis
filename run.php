<?php
require __DIR__.'/vendor/autoload.php';

use CoinRedis\Client;
use CoinRedis\ClusterArray;
use CoinRedis\Cluster;
date_default_timezone_set('Europe/Copenhagen');
/*

$clusterArray = new ClusterArray([
    new Client("127.0.0.1", 6379),
    new Client("127.0.0.1", 6379),
    new Client("127.0.0.1", 6379),
]);

$clusterArray->add("127.0.0.1", 6379);

$cluster = new Cluster($clusterArray);

$cluster->massWrite("test");*/


$client = new Client("127.0.0.1", 6379);

//$client->write("GET title");

$expire = new DateTime("2021-06-09 09:20:00");

$test = $client->add('USER', 1, $expire);

dd($test);