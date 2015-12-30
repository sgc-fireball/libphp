--TEST--
Testing \HRDNS\Types\Stack\FIFO - FIFO
--FILE--
<?php
require_once(__DIR__ . '/../../../../vendor/autoload.php');

use \HRDNS\Types\Stack\FIFO;

$fifo = new FIFO(array(1,2,3));
echo $fifo->pop()."\n";
$fifo->push(4);
echo $fifo->pop()."\n";
echo $fifo->pop()."\n";
echo $fifo->pop()."\n";
?>
--EXPECT--
1
2
3
4
