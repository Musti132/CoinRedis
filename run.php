<?php
require __DIR__.'/vendor/autoload.php';

use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\CliDumper;
use Symfony\Component\VarDumper\VarDumper;
use CoinRedis\Client;
use CoinRedis\ClusterArray;
use CoinRedis\Cluster;

date_default_timezone_set('Europe/Copenhagen');
/*
VarDumper::setHandler(function ($var) {
    $cloner = new VarCloner();
    $dumper = new CliDumper();
    $dumper->dump($cloner->cloneVar($var));
});*/


$clusterArray = [
    new Client("127.0.0.1", 6379),
    new Client("127.0.0.1", 6379),
];

$cluster = new Cluster($clusterArray);

$ttl = new DateTime("2022-05-27 17:20:00");

//$cluster->massSet('USER', "Testis", $ttl);

$client = new Client("127.0.0.1", 6379);

//$test = $client->set('title', "HELLO", $ttl);

//$client->delete('title');

// $test = $client->set('title5', [
//     'test2',
//     'test',
// ], $ttl);

$client->set('title5', null, $ttl);


var_dump($client->get('title5'));
var_dump($client->get('USER'));
var_dump($client->get('not'));