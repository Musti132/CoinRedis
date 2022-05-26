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

    /**
     * Stores a key on the server with a value and optionally a expiration time.
     * 
     * @param string $key Key name to set
     * @param mixed $value Value of key
     * @param DateTime|null $ttl Expiration time on key
     * @throws CoinRedis\Exceptions\NegativeTime Will throw this exception if the expiration time is negative or zero
     * @return string
     */
    public function set(string $key, $value, DateTime $ttl = null)
    {
        $now = new DateTime();

        $seconds = $ttl->format('U') - $now->format('U');

        if ($seconds <= 0) {
            throw new NegativeTime("Calculated time is negative or zero");
        }

        if (is_array($value)) {
            //$writeToSocket = vprintf("SETEX %s %d %s\r\n", $key, $seconds, $value);
            $value = serialize($value);
        }

        $writeToSocket = sprintf("SETEX %s %d '%s'\r\n", $key, $seconds, $value);

        return $this->write($writeToSocket);
    }

    public function getList(string $key)
    {
        $value = $this->get($key);

        //echo $value;

        return json_decode($value);
    }

    /**
     * Delete key(s) from the server
     * 
     * @param string|array $keys Accept both a string or a array of keys
     * @throws CoinRedis\Exceptions\RedisKeyError If key is not found
     * @return string
     */
    public function delete($keys)
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


    /**
     * Update a key
     * 
     * @param string $key Key to update
     * @return mixed
     */
    public function update(string $key, $value, DateTime $ttl)
    {
        if (!$this->get($key)) {
            throw new RedisKeyError("Key doesnt exist");
        }

        return $this->set($key, $value, $ttl);
    }

    /**
     * Delete multiple keys at once.
     * 
     * @param array $keys An Array of keys
     * @return string
     */
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

        if($this->is_serial($data)) {
            return unserialize($data);
        }

        return trim($data);
    }

    /**
     * Check if a string is serialized
     * @param string $string
     */
    public static function is_serial($string)
    {
        return (@unserialize($string) !== false);
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
