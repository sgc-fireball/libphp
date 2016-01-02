--TEST--
Testing HRDNS\SharedMemory\File - return
--FILE--
<?php
$basePath = preg_replace('/\/tests\/.*/','',__DIR__);
require_once($basePath.'/vendor/autoload.php');
use HRDNS\System\Process\Process;
use HRDNS\System\SharedStorage\File;

$process1 = new Process();
$process1->addOption('file',0);
$process1->setCommand(function(Process $Process) {
    $filePath = $Process->getOption('file');
    if (!$filePath ) {
        return 1;
    }
    $file = new File($filePath);
    if (!$file->exists() ) {
        return 2;
    }
    $count = (int)$file->read();
    $count++;
    if (!$file->write($count) ) {
        return 3;
    }
    return 0;
});

$process2 = clone $process1;

$file = new File();
if ( $file->exists() ) {
    printf("Test FilePath %s already exists. Abort test to preventing damage the system!\n",$file->getFile());
    exit(1);
}
if (!$file->write(1) ) {
    printf("Could not write 1 to file %s\n",$file->getFile());
    exit(2);
}

$process1->addOption('file',$file->getFile());
$process2->addOption('file',$file->getFile());

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
printf("SHM Count: %d\n",$file->read());
printf("Delete: %s\n",$file->delete()?'Done':'Fail');
?>
--EXPECT--
Process1: 0
Process2: 0
SHM Count: 3
Delete: Done
