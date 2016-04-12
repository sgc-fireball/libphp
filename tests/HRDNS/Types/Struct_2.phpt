--TEST--
Testing HRDNS\Types\Struct - get serialize
--FILE--
<?php
$basePath = preg_replace('/\/tests\/.*/', '', realpath(__DIR__));
require_once($basePath . '/tests/bootstrap.php');

use HRDNS\Types\Struct;

$struct = new Struct(
    array (
        'test' => 'test',
    )
);
echo $struct->getSerialize();
?>
--EXPECT--
a:1:{s:4:"test";s:4:"test";}