--TEST--
Testing \HRDNS\Core\SignalHandler - catch
--FILE--
<?php
require_once(__DIR__ . '/../../../vendor/autoload.php');
use HRDNS\Core\SignalHandler;

function handler($signal)
{
    switch ($signal) {
        case SignalHandler::SIGINT:
            echo 'SIGINT';
            break;
    }
    return true;
}

SignalHandler::init();
SignalHandler::addListner('handler');
usleep(100);
posix_kill(posix_getpid(),SignalHandler::SIGINT);
usleep(100);
pcntl_signal_dispatch();
usleep(100);
?>
--EXPECT--
SIGINT