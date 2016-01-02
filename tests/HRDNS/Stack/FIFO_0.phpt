--TEST--
Testing HRDNS\Types\Stack\FIFO - push & pop
--FILE--
<?php
$basePath = preg_replace('/\/tests\/.*/','',__DIR__);
require_once($basePath.'/vendor/autoload.php');

use HRDNS\Types\Stack\FIFO;

$fifo = new FIFO(array(1,2,3));
$fifo->push(4);
$fifo->push(5);
$fifo->push(6);

foreach ( $fifo as $val ) {
    echo $val."\n";
}

echo "-\n";

echo $fifo->pop()."\n";
echo $fifo->pop()."\n";
echo $fifo->pop()."\n";
echo $fifo->pop()."\n";
echo $fifo->pop()."\n";
echo $fifo->pop()."\n";

?>
--EXPECT--
1
2
3
4
5
6
-
1
2
3
4
5
6