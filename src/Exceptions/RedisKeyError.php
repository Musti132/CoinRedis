<?php

namespace CoinRedis\Exceptions;

use Exception;

class RedisKeyError extends Exception{
    public function __construct($message, $code = 0, Throwable $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}
