<?php

namespace Hbrawnak\Limitr\Tests;


use Hbrawnak\Limitr\Blocklist;

class BlockListTest extends TestCase
{
    public function testBlocksAndUnblocksKeys()
    {
        $storage = $this->createRedisStorage();
        $blocklist = new Blocklist($storage);
        $key = '192.168.1.1';

        $blocklist->block($key, 60);
        $this->assertTrue($blocklist->isBlocked($key));

        $blocklist->removeBlock($key);
        $this->assertFalse($blocklist->isBlocked($key));
    }

    public function testBlockExpiry()
    {
        $storage = $this->createRedisStorage();
        $blocklist = new Blocklist($storage);
        $key = '192.168.1.1';

        $blocklist->block($key, 1);
        sleep(2);

        $this->assertFalse($blocklist->isBlocked($key));
    }
}
