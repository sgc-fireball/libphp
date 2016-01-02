--TEST--
Testing HRDNS\Types\Stack\FIFO - push & pop
--FILE--
<?php
$basePath = preg_replace('/\/tests\/.*/','',__DIR__);
require_once($basePath.'/vendor/autoload.php');

use HRDNS\Types\Stack\RingBuffer;

$ringBuffer = new RingBuffer(3,array(1,2,3));
$ringBuffer->push(4); // overwrite 1
$ringBuffer->push(5); // overwrite 2
$ringBuffer->push(6); // overwrite 3
$ringBuffer->push(7); // overwrite 4 instance of 1

foreach ( $ringBuffer as $val ) {
    echo $val."\n"; // echo 7 5 6
}

echo "-\n";

echo $ringBuffer->pop().".\n"; // echo 7
echo $ringBuffer->pop().".\n"; // echo 5
echo $ringBuffer->pop().".\n"; // echo 6
echo $ringBuffer->pop().".\n"; // echu null
$ringBuffer->push(1); // add 1
echo $ringBuffer->pop().".\n"; // echo 1
echo $ringBuffer->pop().".\n"; // echo null

?>
--EXPECT--
7
5
6
-
7.
5.
6.
.
1.
.
