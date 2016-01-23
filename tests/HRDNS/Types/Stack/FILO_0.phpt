--TEST--
Testing \HRDNS\Types\Stack\FILO - FILO
--FILE--
<?php
require_once(__DIR__ . '/../../../../vendor/autoload.php');

use \HRDNS\Types\Stack\FILO;

$filo = new FILO(array (1, 2, 3));
echo $filo->pop() . "\n";
$filo->push(4);
echo $filo->pop() . "\n";
echo $filo->pop() . "\n";
echo $filo->pop() . "\n";
?>
--EXPECT--
3
4
2
1
