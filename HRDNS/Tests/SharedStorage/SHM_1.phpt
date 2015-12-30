--TEST--
Testing HRDNS\SharedStorage\SHM - create
--FILE--
<?php
require_once(__DIR__ . '/../../../vendor/autoload.php');
use HRDNS\System\SharedStorage\SHM;

$shm = new SHM(1337);
printf("Exists: %d\n",(int)$shm->exists());
printf("Write: %s\n",$shm->write('test')?'Done':'Fail');
printf("Read: %s\n",$shm->read()=='test'?'Done':'Fail');
printf("Delete: %s\n",$shm->delete()?'Done':'Fail');
?>
--EXPECT--
Exists: 0
Write: Done
Read: Done
Delete: Done
