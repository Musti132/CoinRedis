<?php

namespace CoinRedis;

use CoinRedis\Exceptions\NegativeTime;
use CoinRedis\Exceptions\RedisError;
use CoinRedis\Exceptions\RedisKeyError;
use Socket\Raw\Socket;
use DateTime;

class Client extends Connection
{
    public $data;

    private const DELETE_KEY_DOESNT_EXIST_CODE = 0;
    private const GET_KEY_DOESNT_EXIST_CODE = -1;

    private Socket $connector;

    private array $remove = [
        '$5\r',
        '$5',
        '+',
        ':',
        '$',
    ];

    public function __construct(string $ip, int $port, array $options = null, bool $async = true)
    {
        parent::__construct($ip, $port, $options);

        $this->connector = $this->connection();

        $this->manageAsync($async);
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

    /**
     * Delete key(s) from the server
     * 
     * @param string|array $keys Accept both a string or a array of keys
     * @throws CoinRedis\Exceptions\RedisKeyError If key is not found
     * @return string
     */
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

    /**
     * Get a specific key from the server.
     * 
     * @param string $key
     * @return string 
     */
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
