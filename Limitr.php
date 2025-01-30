<?php

namespace Hbrawnak\Limitr;

use Hbrawnak\Limitr\Contracts\StorageInterface;
use Hbrawnak\Limitr\Contracts\RequestContext;
use Hbrawnak\Limitr\Rules\RateLimitRule;
use Hbrawnak\Limitr\Exceptions\RateLimitExceededException;

class Limitr
{
    private $violationCounts = [];
    private $storage;
    private $blocklist;
    private $rules;

    public function __construct(StorageInterface $storage, Blocklist $blocklist, $rules) {
        $this->rules = $rules;
        $this->blocklist = $blocklist;
        $this->storage = $storage;
    }

    public function check(RequestContext $context)
    {
        $ipKey = 'ip:' . $context->getIp();

        if ($this->blocklist->isBlocked($ipKey)) {
            throw new RateLimitExceededException(
                'IP address blocked',
                0,
                0
            );
        }

        foreach ($this->rules as $rule) {
            $key = $rule->getKey($context);
            $result = $this->storage->increment($key, $rule->interval);

            if ($result['count'] > $rule->maxAttempts) {
                $this->handleViolation($context, $rule);
                $remaining = max(0, $rule->maxAttempts - $result['count']);

                throw new RateLimitExceededException(
                    "Rate limit exceeded for {$rule->name}",
                    $remaining,
                    $result['reset']
                );
            }
        }
    }

    private function handleViolation(RequestContext $context, RateLimitRule $rule)
    {
        if ($rule->blockDuration === null) return;

        $ipKey = 'ip:' . $context->getIp();
        $violationKey = "violations:{$ipKey}";

        $violations = $this->storage->increment($violationKey, 3600)['count'];

        if ($violations >= $rule->blockThreshold) {
            $this->blocklist->block($ipKey, $rule->blockDuration);
            $this->storage->removeBlock($violationKey);
        }
    }

    public function getBlocklist(): Blocklist
    {
        return $this->blocklist;
    }
}