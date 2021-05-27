<?php
namespace CoinRedis;
require __DIR__.'/../vendor/autoload.php';

use React\EventLoop\Factory;
use React\Socket\Connector;
use React\Socket\ConnectionInterface;
use React\Stream\WritableResourceStream;

class Client
{

    private $connector;
    private $loop;
    private $host;

    public function __construct(string $ip, int $port, array $options = null)
    {
        $this->host = $ip.":".$port;
        $this->loop = Factory::create();
        $this->connector = new Connector($this->loop);
    }

    public function write(string $data)
    {
        $loop = $this->loop;

        $this->connector->connect($this->host)->then(function (ConnectionInterface $connection) use ($loop, $data) {
            $connection->write("GET title\r\n");

            $connection->on('data', function ($data) use($connection) {
                $data = str_replace("$5\r", '', $data);
                echo trim($data)."\n";
                $connection->close();
            });
            
            $connection->on('error', function (Exception $e) {
                echo 'error: ' . $e->getMessage() . PHP_EOL;
            });
        });

        $loop->run();
    }

    public function read(){
        $loop = $this->loop;

        $this->connector->connect($this->host)->then(function (ConnectionInterface $connection) use ($loop) {
            $connection->pipe(new React\Stream\WritableResourceStream(STDOUT, $loop));
            
            $connection->on('error', function (Exception $e) {
                echo 'error: ' . $e->getMessage() . PHP_EOL;
            });
        });

        $loop->run();
    }

    public function host(){
        return $this->host;
    }
    
}
