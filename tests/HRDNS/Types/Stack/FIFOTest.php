<?php

namespace Test\HRDNS\Types\Stack;

use HRDNS\Types\Stack\FIFO;

class FIFOTest extends \PHPUnit_Framework_TestCase
{

    /** @var FIFO */
    private $fifo;

    public function setUp()
    {
        $this->fifo = new FIFO([1, 2, 3]);
    }

    public function testPop()
    {
        $this->assertEquals(1, $this->fifo->pop());
        $this->assertEquals(2, $this->fifo->pop());
        $this->assertEquals(3, $this->fifo->pop());
        $this->assertNull($this->fifo->pop());
    }

    public function testPush()
    {
        $this->assertInstanceOf('HRDNS\Types\Stack\FIFO', $this->fifo->push(4));
    }

    public function testPopPushPop()
    {
        $this->assertEquals(1, $this->fifo->pop());
        $this->assertInstanceOf('HRDNS\Types\Stack\FIFO', $this->fifo->push(4));
        $this->assertEquals(2, $this->fifo->pop());
        $this->assertEquals(3, $this->fifo->pop());
        $this->assertEquals(4, $this->fifo->pop());
        $this->assertNull($this->fifo->pop());
    }

}