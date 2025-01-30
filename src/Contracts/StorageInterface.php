<?php

namespace Hbrawnak\Limitr\Contracts;

interface StorageInterface
{
    public function increment($key, $decay);
    public function get($key);
    public function reset($key);
    public function setBlock($key, $duration);
    public function isBlocked($key);
    public function removeBlock($key);
}