--TEST--
Testing \HRDNS\Core\EventHandler - add and fire an event
--FILE--
<?php
$basePath = preg_replace('/\/tests\/.*/','',realpath(__DIR__));
require_once($basePath.'/vendor/autoload.php');

use \HRDNS\Core\EventHandler;

$clicked = false;
$eventHandler = EventHandler::get();
$eventHandler->addEvent('click',function()use(&$clicked){
    $clicked = true;
});

var_dump($clicked);
$eventHandler->fireEvent('click');
var_dump($clicked);
?>
--EXPECT--
bool(false)
bool(true)
