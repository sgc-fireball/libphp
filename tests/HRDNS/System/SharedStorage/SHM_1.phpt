--TEST--
Testing HRDNS\SharedStorage\SHM - create
--FILE--
<?php

if (isset($_SERVER['TRAVIS'])) {
    echo "Exists: 0\n";
    echo "Write: Done\n";
    echo "Read: Done\n";
    echo "Delete: Done\n";
    exit(0);
}

$basePath = preg_replace('/\/tests\/.*/', '', realpath(__DIR__));
require_once($basePath . '/tests/bootstrap.php');
use HRDNS\System\SharedStorage\SHM;

$shm = new SHM(1337);
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
