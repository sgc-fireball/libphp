--TEST--
Testing HRDNS\System\Process\Process - return
--FILE--
<?php
$basePath = preg_replace('/\/tests\/.*/','',__DIR__);
require_once($basePath.'/vendor/autoload.php');

use HRDNS\System\Process\Process;

$process = new Process();
$process->setCommand(function(Process $oProcess) {
    return 10;
});
$process->start();
do {
    usleep(500);
} while ($process->isRunning());
var_dump($process->getExitCode());
?>
--EXPECT--
int(10)