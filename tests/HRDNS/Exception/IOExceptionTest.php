<?php

declare(ticks = 100);

namespace HRDNS\Tests\Core;

use HRDNS\Exception\IOException;

class IOExceptionTest extends \PHPUnit_Framework_TestCase
{

    public function testClassExists()
    {
        $this->assertTrue(class_exists(IOException::class));
        $e = new IOException('TestString',123);
        $this->assertEquals('TestString',$e->getMessage());
        $this->assertEquals(123,$e->getCode());
    }

}
