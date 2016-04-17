--TEST--
Testing \HRDNS\Core\Event - check propagation
--FILE--
<?php
$basePath = preg_replace('/\/tests\/.*/', '', realpath(__DIR__));
require_once($basePath . '/tests/bootstrap.php');
use \HRDNS\Core\Event;

$event = new Event();
echo ($event->isPropagationStopped() ? 1 : 0) . "\n";
$event->stopPropagation();
echo ($event->isPropagationStopped() ? 1 : 0) . "\n";
?>
--EXPECT--
1
0
