--TEST--
Testing \HRDNS\Types\Stack\RingBuffer - RingBuffer
--FILE--
<?php
require_once(__DIR__ . '/../../../../vendor/autoload.php');

use \HRDNS\Types\Stack\RingBuffer;

$ring = new RingBuffer(5,array(1,2,3,4,5));
echo $ring->pop()."\n";
echo $ring->pop()."\n";
$ring->push(1);
$ring->push(1);
$ring->push(1);
$ring->push(1);
$ring->push(1);
echo $ring->pop()."\n";
echo $ring->pop()."\n";
echo $ring->pop()."\n";
echo $ring->pop()."\n";
echo $ring->pop()."\n";
?>
--EXPECT--
1
2
1
1
1
1
1
