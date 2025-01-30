<?php

namespace Hbrawnak\Limitr\Drivers;

use Hbrawnak\Limitr\Contracts\StorageInterface;
use Redis;

class RedisStorage implements StorageInterface
{
    private $redis;

    public function __construct(Redis $redis) {
        $this->redis = $redis;
    }

    public function increment($key, $decay)
    {
        $counterKey = "limiter:{$key}";
        $lua = "
            local current
            current = redis.call('incr', KEYS[1])
            if tonumber(current) == 1 then
                redis.call('expire', KEYS[1], ARGV[1])
            end
            return {current, redis.call('ttl', KEYS[1])}
        ";

        $result = $this->redis->eval($lua, [$counterKey, $decay], 1);
        $count = isset($result[0]) ? $result[0] : 0;
        $ttl = isset($result[1]) ? $result[1] : 0;

        return [
            'count' => $count,
            'reset' => time() + $ttl,
        ];

    }

    public function get($key)
    {
        $counterKey = "limiter:{$key}";
        $count = $this->redis->get($counterKey);
        $ttl = $this->redis->ttl($counterKey);

        if ($count === false) return null;

        return [
            'count' => (int)$count,
            'reset' => time() + $ttl,
        ];
    }

    public function reset($key)
    {
        $this->redis->del("limiter:{$key}");
    }

    public function setBlock($key, $duration)
    {
        $this->redis->setex("blocked:{$key}", $duration, 1);
    }

    public function isBlocked($key)
    {
        return (bool)$this->redis->exists("blocked:{$key}");
    }

    public function removeBlock($key)
    {
        $this->redis->del("blocked:{$key}");
    }
}