--TEST--
Testing HRDNS\Types\Struct - load from serialize
--FILE--
<?php
$basePath = preg_replace('/\/tests\/.*/', '', realpath(__DIR__));
require_once($basePath . '/tests/bootstrap.php');

use HRDNS\Types\Struct;

$struct = new Struct();
$struct->loadFromSerialize('a:1:{s:4:"test";s:4:"test";}');
echo $struct->getSerialize();
?>
--EXPECT--
a:1:{s:4:"test";s:4:"test";}