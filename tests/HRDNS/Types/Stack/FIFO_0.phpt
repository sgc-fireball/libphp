--TEST--
Testing \HRDNS\Types\Stack\FIFO - FIFO
--FILE--
<?php
$basePath = preg_replace('/\/tests\/.*/', '', realpath(__DIR__));
require_once($basePath . '/tests/bootstrap.php');

use \HRDNS\Types\Stack\FIFO;

$fifo = new FIFO(array (1, 2, 3));
echo $fifo->pop() . "\n";
$fifo->push(4);
echo $fifo->pop() . "\n";
echo $fifo->pop() . "\n";
echo $fifo->pop() . "\n";
?>
--EXPECT--
1
2
3
4
