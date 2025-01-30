<?php


use Hbrawnak\Limitr\Tests\TestCase;

class RedisStorageTest extends TestCase
{
    public function testIncrementCountsProperly()
    {
        $storage = $this->createRedisStorage();
        $key = 'test-key';

        $result1 = $storage->increment($key, 60);
        $result2 = $storage->increment($key, 60);

        $this->assertEquals(1, $result1['count']);
        $this->assertEquals(2, $result2['count']);
        $this->assertGreaterThan(time(), $result1['reset']);
    }

    public function testResetsCountAfterExpiry()
    {
        $storage = $this->createRedisStorage();
        $key = 'test-key';

        $storage->increment($key, 1);
        sleep(2); // Wait for expiration

        $result = $storage->get($key);
        $this->assertNull($result);
    }

    public function testBlocksKeysProperly()
    {
        $storage = $this->createRedisStorage();
        $key = 'blocked-ip';

        $storage->setBlock($key, 60);
        $this->assertTrue($storage->isBlocked($key));

        $storage->removeBlock($key);
        $this->assertFalse($storage->isBlocked($key));
    }
}