<?php
require __DIR__.'/vendor/autoload.php';

use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\CliDumper;
use Symfony\Component\VarDumper\VarDumper;

date_default_timezone_set('Europe/Copenhagen');

VarDumper::setHandler(function ($var) {
    $cloner = new VarCloner();
    $dumper = new CliDumper();
    $dumper->dump($cloner->cloneVar($var));
});

use CoinRedis\Client;
use CoinRedis\ClusterArray;
use CoinRedis\Cluster;

$clusterArray = new ClusterArray([
    new Client("127.0.0.1", 6379),
    new Client("127.0.0.1", 6379),
]);

$clusterArray->add("127.0.0.1", 6379);
$clusterArray->add("127.0.0.1", 6379);

$cluster = new Cluster($clusterArray);

$expire = new DateTime("2021-07-09 09:20:00");

$cluster->massSet('USE2R', 1, $expire);

$client = new Client("127.0.0.1", 6379);

$test = $client->set('title', "HELLO", $expire);

$client->delete('title');

$test = $client->set('title', "HELLO", $expire);

dump($client->get("title"));

dump($test);