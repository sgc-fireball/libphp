<?php declare(strict_types=1);

namespace HRDNS\Tests\Types\Stack;

use HRDNS\Types\Stack\FILO;

class FILOTest extends \PHPUnit\Framework\TestCase
{

    /** @var FILO */
    private $filo;

    protected function setUp(): void
    {
        $this->filo = new FILO([1, 2, 3]);
    }

    public function testPop()
    {
        $this->assertEquals(3, $this->filo->pop());
        $this->assertEquals(2, $this->filo->pop());
        $this->assertEquals(1, $this->filo->pop());
        $this->assertNull($this->filo->pop());
    }

    public function testPush()
    {
        $this->assertInstanceOf('HRDNS\Types\Stack\FILO', $this->filo->push(4));
    }

    public function testPopPushPop()
    {
        $this->assertEquals(3, $this->filo->pop());
        $this->assertInstanceOf('HRDNS\Types\Stack\FILO', $this->filo->push(4));
        $this->assertEquals(4, $this->filo->pop());
        $this->assertEquals(2, $this->filo->pop());
        $this->assertEquals(1, $this->filo->pop());
        $this->assertNull($this->filo->pop());
    }

}
