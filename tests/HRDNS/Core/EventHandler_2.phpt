--TEST--
Testing \HRDNS\Core\EventHandler - stopPropagation
--FILE--
<?php
$basePath = preg_replace('/\/tests\/.*/', '', realpath(__DIR__));
require_once($basePath . '/tests/bootstrap.php');

use \HRDNS\Core\EventHandler;
use \HRDNS\Core\Event;

$clicked = 0;
$eventHandler = EventHandler::get();
$eventHandler->addEvent(
    'click',
    function (Event $event) use (&$clicked) {
        $clicked++;
        $event->stopPropagation();
    }
);
$eventHandler->addEvent(
    'click',
    function (Event $event) use (&$clicked) {
        $clicked++;
    }
);

var_dump($clicked);
$eventHandler->fireEvent('click');
var_dump($clicked);
?>
--EXPECT--
int(0)
int(1)
