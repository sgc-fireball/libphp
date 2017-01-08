<?php

namespace HRDNS\Tests\Types\Stack;

use HRDNS\Types\Stack\RingBuffer;

class RingBufferTest extends \PHPUnit_Framework_TestCase
{

    /** @var RingBuffer */
    private $ringBuffer;

    public function setUp()
    {
        $this->ringBuffer = new RingBuffer(5, [1, 2, 3, 4, 5]);
    }

    public function testPop()
    {
        $this->assertEquals(1, $this->ringBuffer->pop());
        $this->assertEquals(2, $this->ringBuffer->pop());
        $this->assertEquals(3, $this->ringBuffer->pop());
        $this->assertEquals(4, $this->ringBuffer->pop());
        $this->assertEquals(5, $this->ringBuffer->pop());
        $this->assertNull($this->ringBuffer->pop());
    }

    public function testPush()
    {
        $this->assertInstanceOf('HRDNS\Types\Stack\RingBuffer', $this->ringBuffer->push(4));
    }

    public function testPopPushPop()
    {
        $this->assertEquals(1, $this->ringBuffer->pop());
        $this->assertInstanceOf('HRDNS\Types\Stack\RingBuffer', $this->ringBuffer->push(4));
        $this->assertEquals(2, $this->ringBuffer->pop());
        $this->assertEquals(3, $this->ringBuffer->pop());
        $this->assertEquals(4, $this->ringBuffer->pop());
        $this->assertEquals(5, $this->ringBuffer->pop());
        $this->assertEquals(4, $this->ringBuffer->pop());
        $this->assertNull($this->ringBuffer->pop());
    }

    public function testOverwrite()
    {
        $this->assertInstanceOf('HRDNS\Types\Stack\RingBuffer', $this->ringBuffer->push(6));
        $this->assertInstanceOf('HRDNS\Types\Stack\RingBuffer', $this->ringBuffer->push(7));
        $this->assertInstanceOf('HRDNS\Types\Stack\RingBuffer', $this->ringBuffer->push(8));
        $this->assertInstanceOf('HRDNS\Types\Stack\RingBuffer', $this->ringBuffer->push(9));
        $this->assertInstanceOf('HRDNS\Types\Stack\RingBuffer', $this->ringBuffer->push(0));
        $this->assertEquals(6, $this->ringBuffer->pop());
        $this->assertEquals(7, $this->ringBuffer->pop());
        $this->assertEquals(8, $this->ringBuffer->pop());
        $this->assertEquals(9, $this->ringBuffer->pop());
        $this->assertEquals(0, $this->ringBuffer->pop());
    }

}
