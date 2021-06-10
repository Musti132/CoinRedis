<?php

namespace CoinRedis;

require __DIR__ . '/../vendor/autoload.php';

use CoinRedis\Exceptions\NegativeTime;
use CoinRedis\Exceptions\RedisError;
use CoinRedis\Exceptions\RedisKeyError;
use Socket\Raw\Factory;
use Socket\Raw\Socket;
use DateTime;

class Client
{
    public $data;

    private const DELETE_KEY_DOESNT_EXIST_CODE = 0;
    private const GET_KEY_DOESNT_EXIST_CODE = -1;
    private $connector;
    private $factory;
    private $host;

    private array $remove = [
        '$5\r',
        '$5',
        '+',
        ':',
        '$',
    ];

    public function __construct(string $ip, int $port, array $options = null)
    {
        $this->host = $ip . ":" . $port;

        $this->factory = new Factory();

        $this->connector = $this->factory->createClient($this->host);
    }

    public function write(string $data)
    {
        $this->connector->write($data . "\r\n");

        $data = $this->read($this->connector);

        return $data;
    }

    public function read(Socket $socket)
    {
        $data = $socket->read(8192);

        $formatted = $this->formatData($data);

        return $formatted;
    }

    public function set(string $key, mixed $value, DateTime $ttl = null)
    {
        $now = new DateTime();

        $seconds = $ttl->format('U') - $now->format('U');

        if ($seconds <= 0) {
            throw new NegativeTime("Calculated time is negative or zero");
        }

        $writeToSocket = sprintf("SETEX %s %d %s\r\n", $key, $seconds, $value);

        return $this->write($writeToSocket);
    }

    public function delete(string|array $keys)
    {
        if (is_array($keys)) {
            return $this->deleteMultiple($keys);
        }

        $writeToSocket = sprintf("DEL %s\r\n", $keys);

        $data = $this->write($writeToSocket);

        if ($data == self::DELETE_KEY_DOESNT_EXIST_CODE) {
            throw new RedisKeyError("Key doesnt exist");
        }

        return $data;
    }

    public function deleteMultiple(array $keys)
    {
        foreach ($keys as $key) {
            if (!$this->get($key)) {
                throw new RedisKeyError("Key doesnt exist");
            }
        }

        $keys = implode(' ', $keys);

        return $this->write(sprintf("DEL %s", $keys));
    }

    public function get(string $key)
    {
        $writeToSocket = sprintf("GET %s", $key);

        $data = $this->write($writeToSocket);

        if ($data == self::GET_KEY_DOESNT_EXIST_CODE) {
            throw new RedisKeyError("Key doesnt exist");
        }

        return $data;
    }

    public function isOk($data)
    {
        return $data != "0" ? true : false;
    }

    public function host()
    {
        return $this->host;
    }

    public function formatData($data)
    {
        return $data = trim(str_replace($this->remove, '', $data));
    }
}
