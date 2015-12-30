--TEST--
Testing HRDNS\SharedMemory\SHM - return
--FILE--
<?php
require_once(__DIR__ . '/../../../vendor/autoload.php');
use HRDNS\System\Process\Process;
use HRDNS\System\SharedStorage\SHM;

$process1 = new Process();
$process1->addOption('shm',0);
$process1->setCommand(function(Process $Process) {
    $shmKey = $Process->getOption('shm');
    if (!$shmKey ) {
        return 1;
    }
    $shm = new SHM($shmKey);
    if (!$shm->exists() ) {
        return 2;
    }
    $count = (int)$shm->read();
    $count++;
    if (!$shm->write($count) ) {
        return 3;
    }
    return 0;
});

$process2 = clone $process1;

$shm = new SHM();
if ( $shm->exists() ) {
    printf("Test SHM Key %d already exists. Abort test to preventing damage the system!\n",$shm->getKey());
    exit(1);
}
if (!$shm->write(1) ) {
    printf("Could not write 1 to shm key %d\n",$shm->getKey());
    exit(2);
}

$process1->addOption('shm',$shm->getKey());
$process2->addOption('shm',$shm->getKey());

if ( $process1->start() ) {
    do {
        usleep(500);
    } while ( $process1->isRunning() );
}
if ( $process2->start() ) {
    do {
        usleep(500);
    } while ( $process2->isRunning() );
}

printf("Process1: %d\n",$process1->getExitCode());
printf("Process2: %d\n",$process2->getExitCode());
printf("SHM Count: %d\n",$shm->read());
printf("Delete: %s\n",$shm->delete()?'Done':'Fail');
?>
--EXPECT--
Process1: 0
Process2: 0
SHM Count: 3
Delete: Done
