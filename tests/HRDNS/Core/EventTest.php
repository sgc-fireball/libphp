<?php declare(strict_types=1);

namespace HRDNS\Tests\Core;

use HRDNS\Core\Event;

class EventTest extends \PHPUnit\Framework\TestCase
{

    public function testPropagationStatus()
    {
        $event = new Event();
        $this->assertTrue($event->isPropagationStopped());
        $event->stopPropagation();
        $this->assertFalse($event->isPropagationStopped());
    }

}
