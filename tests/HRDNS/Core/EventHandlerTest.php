<?php

declare(ticks = 100);

namespace HRDNS\Tests\Core;

use HRDNS\Core\Event;
use HRDNS\Core\EventHandler;

class EventHandlerTest extends \PHPUnit_Framework_TestCase
{

    /** @var EventHandler */
    private $eventHandler;

    public function setUp()
    {
        $this->eventHandler = EventHandler::get();
    }

    public function testClick()
    {
        $clicked = false;
        $this->eventHandler->addEvent(
            __METHOD__,
            function () use (&$clicked) {
                $clicked = true;
            }
        );

        $this->assertFalse($clicked);
        $this->eventHandler->fireEvent(__METHOD__);
        $this->assertTrue($clicked);
    }

    public function testClicks()
    {
        $clicks = 0;
        $this->eventHandler->addEvent(
            __METHOD__,
            function () use (&$clicks) {
                $clicks++;
            }
        );

        $this->assertEquals(0,$clicks);
        $this->eventHandler->fireEvent(__METHOD__);
        $this->assertEquals(1,$clicks);
        $this->eventHandler->fireEvent(__METHOD__);
        $this->assertEquals(2,$clicks);
    }

    public function testClicksWithTwoEvents()
    {
        $clicks = 0;
        $this->eventHandler->addEvent(
            __METHOD__,
            function () use (&$clicks) {
                $clicks++;
            }
        );
        $this->eventHandler->addEvent(
            __METHOD__,
            function () use (&$clicks) {
                $clicks++;
            }
        );

        $this->assertEquals(0,$clicks);
        $this->eventHandler->fireEvent(__METHOD__);
        $this->assertEquals(2,$clicks);
    }

    public function testClicksWithStopPropagation()
    {
        $clicks = 0;
        $this->eventHandler->addEvent(
            __METHOD__,
            function (Event $event) use (&$clicks) {
                $clicks++;
                $event->stopPropagation();
            }
        );
        $this->eventHandler->addEvent(
            __METHOD__,
            function () use (&$clicks) {
                $clicks++;
            }
        );

        $this->assertEquals(0,$clicks);
        $this->eventHandler->fireEvent(__METHOD__);
        $this->assertEquals(1,$clicks);
    }

    public function testClicksWithStopPropagationAndPriority()
    {
        $clicks = 0;
        $this->eventHandler->addEvent(
            __METHOD__,
            function (Event $event) use (&$clicks) {
                $clicks++;
                $event->stopPropagation();
            }
        );
        $this->eventHandler->addEvent(
            __METHOD__,
            function () use (&$clicks) {
                $clicks++;
            },
            -1
        );

        $this->assertEquals(0,$clicks);
        $this->eventHandler->fireEvent(__METHOD__);
        $this->assertEquals(2,$clicks);
    }

    public function testTicks()
    {
        $ticks = 0;
        $this->eventHandler->addEvent(
            'tick',
            function () use (&$ticks) {
                $ticks++;
            }
        );
        for ($i = 0 ; $i < 100 ; $i++) {
            usleep(100);
        }
        $this->assertTrue($ticks > 0);
    }

    public function testShutdown()
    {
        /**
         * phpunit could not test shutdown functions ...
         * so we test if a test exists that can test it.
         */
        $this->assertTrue(file_exists(__DIR__.'/EventHandlerShutdown.phpt'));
    }

}
