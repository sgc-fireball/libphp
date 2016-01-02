--TEST--
Testing \HRDNS\Core\Event - check propagation
--FILE--
<?php
$basePath = preg_replace('/\/tests\/.*/','',__DIR__);
require_once($basePath.'/vendor/autoload.php');
use \HRDNS\Core\Event;

$event = new Event();
echo ($event->getPropagationStatus() ? 1 : 0) ."\n";
$event->stopPropagation();
echo ($event->getPropagationStatus() ? 1 : 0) ."\n";
?>
--EXPECT--
1
0
