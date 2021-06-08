<?php

namespace CoinRedis;

require __DIR__ . '/../vendor/autoload.php';

use CoinRedis\Exceptions\NegativeTime;
use CoinRedis\Exceptions\RedisError;
use React\EventLoop\Factory;
use React\Socket\Connector;
use React\Socket\ConnectionInterface;
use React\Stream\WritableResourceStream;
use DateTime;

class Client
{
    public $data;

    private $connector;
    private \React\EventLoop\StreamSelectLoop $loop;
    private $host;

    private array $remove = [
        '$5\r',
        '+',
    ];

    public function __construct(string $ip, int $port, array $options = null)
    {
        $this->host = $ip . ":" . $port;
        $this->loop = Factory::create();
        $this->connector = new Connector($this->loop);
    }

    public function write(string $data)
    {
        $loop = $this->loop;

        $this->connector->connect($this->host)->then(function (ConnectionInterface $connection) use ($loop, $data) {
            $connection->write("GET title\r\n");

            $connection->on('data', function ($data) use ($connection) {
                $data = str_replace("$5\r", '', $data);
                echo trim($data) . "\n";
                $connection->close();
            });

            $connection->on('error', function (Exception $e) {
                echo 'error: ' . $e->getMessage() . PHP_EOL;
            });
        });

        $loop->run();
    }

    public function read()
    {
        $loop = $this->loop;

        $this->connector->connect($this->host)->then(function (ConnectionInterface $connection) use ($loop) {
            $connection->pipe(new React\Stream\WritableResourceStream(STDOUT, $loop));

            $connection->on('error', function (Exception $e) {
                echo 'error: ' . $e->getMessage() . PHP_EOL;
            });
        });

        $loop->run();
    }

    public function add($key, $value, DateTime $ttl = null)
    {
        $now = new DateTime();

        $seconds = $ttl->format('U') - $now->format('U');

        if ($seconds <= 0) {
            throw new NegativeTime("Calculated time is negative or zero");
        }

        $loop = $this->loop;

        $this->connector->connect($this->host)->then(function (ConnectionInterface $connection) use ($loop, $key, $value, $seconds) {

            $connection->write(sprintf("SETEX %s %d %s\r\n", $key, $seconds, $value));

            $connection->on('data', function ($data) use ($connection) {
                $data = trim(str_replace($this->remove, '', $data));

                if (!$this->isOk($data)) {
                    throw new RedisError("Error message: " . $data);
                }

                $this->data = $data;

                $connection->close();
            });

            $connection->on('error', function (Exception $e) {
                throw new RedisError('Error: ' . $e->getMessage() . PHP_EOL);
            });
        });

        $loop->run();

        return $this->data;
    }

    public function isOk($data)
    {
        return ($data === "OK") ? true : false;
    }

    public function host()
    {
        return $this->host;
    }
}
