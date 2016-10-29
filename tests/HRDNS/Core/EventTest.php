<?php

namespace Test\HRDNS\Core;

use HRDNS\Core\Event;

class EventTest extends \PHPUnit_Framework_TestCase
{

    public function testPropagationStatus()
    {
        $event = new Event();
        $this->assertTrue($event->isPropagationStopped());
        $event->stopPropagation();
        $this->assertFalse($event->isPropagationStopped());
    }

}
