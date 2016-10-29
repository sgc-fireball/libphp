--TEST--
Testing HRDNS\Types\Struct - get json
--FILE--
<?php
$basePath = preg_replace('/\/tests\/.*/', '', realpath(__DIR__));
require_once($basePath . '/tests/bootstrap.php');

use HRDNS\Types\Struct;

$struct = new Struct(
    array (
        'test' => 'test'
    )
);
echo $struct->getJSON();
?>
--EXPECT--
{"test":"test"}