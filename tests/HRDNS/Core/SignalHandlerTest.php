<?php

namespace HRDNS\Tests\Core;

use HRDNS\Core\SignalHandler;

class SignalHandlerTest extends \PHPUnit_Framework_TestCase
{

    static private $terminated = false;

    public static function signalHandler(int $signal)
    {
        if ($signal == SignalHandler::SIGINT) {
            self::$terminated = true;
        }
        return true;
    }

    public function setUp()
    {
        SignalHandler::init();
        SignalHandler::addListener(
            function($signal) {
                return SignalHandlerTest::signalHandler($signal);
            }
        );
    }

    public function testSignal()
    {
        $this->assertFalse(self::$terminated);
        usleep(100);
        posix_kill(posix_getpid(), SignalHandler::SIGINT);
        usleep(100);
        pcntl_signal_dispatch();
        usleep(100);
        $this->assertTrue(self::$terminated);
    }

}
