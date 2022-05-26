<?php

declare(strict_types=1);

use CoinRedis\Client;
use CoinRedis\Exceptions\RedisKeyError;
use PHPUnit\Framework\TestCase;

final class GetTest extends TestCase
{
    public function testCanGetAKey(): void
    {
        $client = new Client("127.0.0.1", 6379);

        $ttl = new DateTime("2022-05-27 17:20:00");

        $client->set('testing_key', "Testing", $ttl);

        $value = $client->get('testing_key');

        $this->assertEquals($value, "Testing");
    }

    public function testCanGetEmptyKey(): void
    {
        $client = new Client("127.0.0.1", 6379);

        $ttl = new DateTime("2022-05-27 17:20:00");

        $client->set('empty_key', null, $ttl);

        $value = $client->get('empty_key');

        $this->assertIsString($value);
    }

    public function testThrowsExceptionOnNonExistentKey(): void
    {
        $this->expectException(RedisKeyError::class);

        $client = new Client("127.0.0.1", 6379);

        $client->get('non_existent_key');
    }
}
