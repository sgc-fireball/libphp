<?php

namespace HRDNS\Tests\MessageQueue;

use HRDNS\System\MessageQueue\Driver\RedisDriver;
use HRDNS\System\MessageQueue\MessageQueue;
use HRDNS\System\MessageQueue\MessageQueueInterface;


class MessageQueueTest extends \PHPUnit_Framework_TestCase
{

    /** @var string */
    private $testQueue;

    /** @var strinfg */
    private $random;

    public function setUp()
    {
        $this->testQueue = $this->testQueue ?? 'test:'.hash('sha256',microtime(true));
        $this->random = hash('sha256', microtime(true));
    }

    public function testMessageQueueArrayDriver()
    {
        $messageQueue = new MessageQueue($this->testQueue);
        $this->assertNull($messageQueue->next());
        $messageQueue->add(['test' => $this->random]);
        $value = $messageQueue->next();
        $this->assertTrue(is_array($value));
        $this->assertArrayHasKey('test', $value);
        $this->assertEquals($this->random, $value['test']);
        $this->assertNull($messageQueue->next());
    }

    public function testMessageQueueRedisDriver()
    {
        if (!class_exists('Redis')) {
            $this->markTestSkipped('Could not found redis extension.');
        }
        try {
            $redis = new \Redis();
            if (!$redis->connect('127.0.0.1')) {
                throw new \RedisException('Could not connect to 127.0.0.1:6379');
            }
        } catch (\RedisException $e) {
            $this->markTestSkipped($e->getMessage());
            }
        $redisDriver = new RedisDriver($redis);
        $messageQueue = new MessageQueue($this->testQueue, $redisDriver);
        $messageQueue->add(['test' => $this->random]);
        $value = $messageQueue->next();
        $this->assertTrue(is_array($value));
        $this->assertArrayHasKey('test', $value);
        $this->assertEquals($this->random, $value['test']);
        $this->assertNull($messageQueue->next());
    }

}
