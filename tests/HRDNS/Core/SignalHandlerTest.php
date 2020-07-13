<?php declare(strict_types=1);

namespace HRDNS\Tests\Core;

use HRDNS\Core\SignalHandler;

class SignalHandlerTest extends \PHPUnit\Framework\TestCase
{

    static private $terminated = false;

    static private $listenerId = '';

    public static function signalHandler(int $signal)
    {
        if ($signal == SignalHandler::SIGINT) {
            self::$terminated = true;
        }
        return self::$terminated;
    }

    protected function setUp(): void
    {
        SignalHandler::init();
        self::$listenerId = SignalHandler::addListener(
            function($signal) {
                return SignalHandlerTest::signalHandler($signal);
            }
        );
    }

    public function testSignal()
    {
        $this->assertFalse(SignalHandler::removeListener('asdasdsd'));
        $this->assertFalse(self::$terminated);
        $this->assertFalse(SignalHandler::hasTerminated());
        usleep(100);
        posix_kill(posix_getpid(), SignalHandler::SIGINT);
        usleep(100);
        pcntl_signal_dispatch();
        usleep(100);
        $this->assertTrue(self::$terminated);
        $this->assertTrue(SignalHandler::hasTerminated());
        $this->assertTrue(SignalHandler::removeListener(self::$listenerId));
    }

}
