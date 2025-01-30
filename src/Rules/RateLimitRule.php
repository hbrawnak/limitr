<?php

namespace Hbrawnak\Limitr\Rules;

use Hbrawnak\Limitr\Contracts\RequestContext;

class RateLimitRule
{
    public $maxAttempts;
    public $name;
    public $interval;
    public $keySource = 'ip';
    public $blockDuration = null;
    public $blockThreshold = 5;

    public function __construct($name, $maxAttempts, $interval, $keySource = 'ip', $blockDuration = null, $blockThreshold = 5) {

        $this->blockThreshold = $blockThreshold;
        $this->blockDuration = $blockDuration;
        $this->keySource = $keySource;
        $this->interval = $interval;
        $this->name = $name;
        $this->maxAttempts = $maxAttempts;

        if (!in_array($this->keySource, ['ip', 'user_id', 'endpoint'])) {
            throw new \InvalidArgumentException('Invalid key source');
        }
    }

    public function getKey(RequestContext $context)
    {
        switch ($this->keySource) {
            case 'ip':
                return 'ip:' . $context->getIp();
            case 'user_id':
                return 'user:' . ($context->getUserId() !== null ? $context->getUserId() : 'unknown');
            case 'endpoint':
                return 'endpoint:' . $context->getEndpoint();
            default:
                return 'unknown';
        }
    }

}