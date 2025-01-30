<?php

namespace Hbrawnak\Limitr\Tests;

use Hbrawnak\Limitr\Drivers\RedisStorage;
use PHPUnit\Framework\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function createRedisStorage(): RedisStorage
    {
        $redis = new Redis();
        $redis->connect('127.0.0.1');
        $redis->flushAll();
        return new RedisStorage($redis);
    }
}