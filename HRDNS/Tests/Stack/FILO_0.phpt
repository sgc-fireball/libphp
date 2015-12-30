--TEST--
Testing HRDNS\Types\Stack\FIFO - push & pop
--FILE--
<?php
require_once(__DIR__ . '/../../../vendor/autoload.php');
use HRDNS\Types\Stack\FILO;
$filo = new FILO(array(1,2,3));
$filo->push(4);
$filo->push(5);
$filo->push(6);

foreach ( $filo as $val ) {
    echo $val."\n";
}

echo "-\n";

echo $filo->pop()."\n";
echo $filo->pop()."\n";
echo $filo->pop()."\n";
echo $filo->pop()."\n";
echo $filo->pop()."\n";
echo $filo->pop()."\n";

?>
--EXPECT--
6
5
4
3
2
1
-
6
5
4
3
2
1