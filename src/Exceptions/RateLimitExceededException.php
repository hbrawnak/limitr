<?php

namespace Hbrawnak\Limitr\Exceptions;

use Exception;

class RateLimitExceededException extends Exception
{
    private $remaining;
    private $resetTime;

    public function __construct($message, $remaining, $resetTime, $code = 429, $previous = null) {
        $this->resetTime = $resetTime;
        $this->remaining = $remaining;
        parent::__construct($message, $code, $previous);
    }

    public function getRemaining()
    {
        return $this->remaining;
    }

    public function getResetTime()
    {
        return $this->resetTime;
    }

    public function getHeaders()
    {
        return [
            'X-RateLimit-Limit' => $this->remaining,
            'X-RateLimit-Reset' => $this->resetTime,
            'Retry-After' => $this->resetTime - time()
        ];
    }
}
