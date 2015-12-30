--TEST--
Testing HRDNS\System\Process\Process - include return
--FILE--
<?php
require_once(__DIR__ . '/../../../../vendor/autoload.php');

$tmpFile = tempnam('/tmp','phpunit');
file_put_contents($tmpFile,'<?php return 10; ?>');

use HRDNS\System\Process\Process;
$process = new Process();
$process->setCommand($tmpFile);
$process->start();

do { usleep(500); } while ($process->isRunning());

var_dump($process->getExitCode());
unlink($tmpFile);
?>
--EXPECT--
int(10)