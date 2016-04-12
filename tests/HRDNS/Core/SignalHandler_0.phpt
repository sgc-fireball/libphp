--TEST--
Testing \HRDNS\Core\SignalHandler - catch
--FILE--
<?php
$basePath = preg_replace('/\/tests\/.*/', '', realpath(__DIR__));
require_once($basePath . '/tests/bootstrap.php');

use HRDNS\Core\SignalHandler;

function handler($signal)
{
    switch ($signal) {
        case SignalHandler::SIGINT:
            echo "SIGINT\n";
            break;
    }
    return true;
}

SignalHandler::init();
SignalHandler::addListener('handler');
usleep(100);
posix_kill(posix_getpid(), SignalHandler::SIGINT);
usleep(100);
pcntl_signal_dispatch();
usleep(100);
?>
--EXPECT--
SIGINT