--TEST--
Testing HRDNS\Types\Struct - load from json
--FILE--
<?php
$basePath = preg_replace('/\/tests\/.*/', '', realpath(__DIR__));
require_once($basePath . '/tests/bootstrap.php');

use HRDNS\Types\Struct;

$struct = new Struct();
$struct->loadFromJSON('{"test":"test"}');
echo $struct->getJSON();
?>
--EXPECT--
{"test":"test"}