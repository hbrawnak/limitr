<?php

namespace Hbrawnak\Limitr\Tests;

use Hbrawnak\Limitr\Blocklist;
use Hbrawnak\Limitr\Limitr;
use Hbrawnak\Limitr\Rules\RateLimitRule;
use Hbrawnak\Limitr\Exceptions\RateLimitExceededException;
use Hbrawnak\Limitr\Contracts\RequestContext;

class RateLimiterTest extends TestCase
{
    private function createRequestContext($ip = '192.168.1.1', $userId = null, $endpoint = '/api')
    {
        return new class($ip, $userId, $endpoint) implements RequestContext {
            private $ip;
            private $userId;
            private $endpoint;

            public function __construct($ip, $userId, $endpoint)
            {
                $this->ip = $ip;
                $this->userId = $userId;
                $this->endpoint = $endpoint;
            }

            public function getIp()
            {
                return $this->ip;
            }

            public function getUserId()
            {
                return $this->userId;
            }

            public function getEndpoint()
            {
                return $this->endpoint;
            }
        };
    }

    public function testAllowsRequestsUnderLimit()
    {
        $storage = $this->createRedisStorage();
        $limiter = new Limitr(
            $storage,
            new Blocklist($storage),
            [new RateLimitRule('test', 5, 60)]
        );

        for ($i = 0; $i < 5; $i++) {
            $limiter->check($this->createRequestContext());
        }

        $this->assertTrue(true); // No exception thrown
    }

    public function testBlocksExcessiveRequests()
    {
        $storage = $this->createRedisStorage();
        $limiter = new Limitr(
            $storage,
            new Blocklist($storage),
            [new RateLimitRule('test', 2, 60)]
        );

        $this->expectException(RateLimitExceededException::class);

        for ($i = 0; $i < 3; $i++) {
            $limiter->check($this->createRequestContext());
        }
    }

    public function testAutoBlocksAfterThreshold()
    {
        $storage = $this->createRedisStorage();
        $limiter = new Limitr(
            $storage,
            new Blocklist($storage),
            [new RateLimitRule('test', 1, 60, 'ip', 3600, 2)]
        );

        // First violation
        try {
            $limiter->check($this->createRequestContext());
            $limiter->check($this->createRequestContext());
        } catch (RateLimitExceededException $e) {}

        // Second violation
        try {
            $limiter->check($this->createRequestContext());
            $limiter->check($this->createRequestContext());
        } catch (RateLimitExceededException $e) {}

        // Should be blocked now
        $this->expectException(RateLimitExceededException::class);
        $limiter->check($this->createRequestContext());
    }
}
