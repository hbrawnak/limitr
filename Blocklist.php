<?php

namespace Hbrawnak\Limitr;


use Hbrawnak\Limitr\Contracts\StorageInterface;

class Blocklist
{
    private $storage;

    public function __construct(StorageInterface $storage) {
        $this->storage = $storage;
    }

    public function block($key, $duration)
    {
        $this->storage->setBlock("blocked:{$key}", $duration);
    }

    public function isBlocked($key)
    {
        return $this->storage->isBlocked("blocked:{$key}");
    }

    public function removeBlock($key)
    {
        $this->storage->removeBlock("blocked:{$key}");
    }
}