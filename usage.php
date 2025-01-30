<?php

use Hbrawnak\Limitr\Contracts\RequestContext;
use Hbrawnak\Limitr\Drivers\RedisStorage;
use Hbrawnak\Limitr\Exceptions\RateLimitExceededException;
use Hbrawnak\Limitr\Limitr;
use Hbrawnak\Limitr\Rules\RateLimitRule;
use Hbrawnak\Limitr\Blocklist;


$redis = new Redis();
$redis->connect('127.0.0.1');
$storage = new RedisStorage($redis);

// Configure rules
$rules = [
    new RateLimitRule('api-ip-limit', 100, 60, 'ip', 3600, 5),
    new RateLimitRule('user-limit', 1000, 3600, 'user_id')
];


// Create rate limitr instance
$blocklist = new Blocklist($storage);
$limiter = new Limitr($storage, $blocklist, $rules);

// Implement RequestContext
class MyRequestContext implements RequestContext
{
    public function getIp()
    {
        return $_SERVER['REMOTE_ADDR'];
    }

    public function getUserId()
    {
        return Auth::id() ?? null;
    }

    public function getEndpoint()
    {
        return $_SERVER['REQUEST_URI'];
    }
}

try {
    $context = new MyRequestContext();
    $limiter->check($context);

    // Application logic here

} catch (RateLimitExceededException $e) {
    http_response_code(429);
    foreach ($e->getHeaders() as $name => $value) {
        header("$name: $value");
    }
    exit;
}