--TEST--
Testing HRDNS\SharedStorage\File - create
--FILE--
<?php
$basePath = preg_replace('/\/tests\/.*/', '', realpath(__DIR__));
require_once($basePath . '/tests/bootstrap.php');
use HRDNS\System\SharedStorage\File;

$shm = new File();
printf("Exists: %d\n", (int)$shm->exists());
printf("Write: %s\n", $shm->write('test') ? 'Done' : 'Fail');
printf("Read: %s\n", $shm->read() == 'test' ? 'Done' : 'Fail');
printf("Delete: %s\n", $shm->delete() ? 'Done' : 'Fail');
?>
--EXPECT--
Exists: 0
Write: Done
Read: Done
Delete: Done
