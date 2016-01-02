--TEST--
Testing HRDNS\Types\Struct - get json
--FILE--
<?php
require_once(__DIR__ . '/../../../vendor/autoload.php');
use HRDNS\Types\Struct;
$struct = new Struct(array(
    'test' => 'test'
));
echo $struct->getJSON();
?>
--EXPECT--
{"test":"test"}