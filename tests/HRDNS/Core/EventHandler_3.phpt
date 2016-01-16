--TEST--
Testing \HRDNS\Core\EventHandler - stopPropagation with priority
--FILE--
<?php
$basePath = preg_replace('/\/tests\/.*/','',realpath(__DIR__));
require_once($basePath.'/vendor/autoload.php');

use \HRDNS\Core\EventHandler;
use \HRDNS\Core\Event;

$clicked = 0;
$eventHandler = EventHandler::get();
$eventHandler->addEvent('click',function(Event $event)use(&$clicked){
    $clicked++;
    $event->stopPropagation();
});
$eventHandler->addEvent('click',function(Event $event)use(&$clicked){
    $clicked++;
},-1);

var_dump($clicked);
$eventHandler->fireEvent('click');
var_dump($clicked);
?>
--EXPECT--
int(0)
int(2)
