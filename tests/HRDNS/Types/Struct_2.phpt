--TEST--
Testing HRDNS\Types\Struct - get serialize
--FILE--
<?php
require_once(__DIR__ . '/../../../vendor/autoload.php');
use HRDNS\Types\Struct;
$struct = new Struct(array(
    'test' => 'test',
));
echo $struct->getSerialize();
?>
--EXPECT--
a:1:{s:4:"test";s:4:"test";}