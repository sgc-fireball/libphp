--TEST--
Testing HRDNS\System\Process\Process - include exit
--FILE--
<?php
$basePath = preg_replace('/\/tests\/.*/','',__DIR__);
require_once($basePath.'/vendor/autoload.php');

use HRDNS\System\Process\Process;

$tmpFile = tempnam('/tmp','phpunit');
file_put_contents($tmpFile,'<?php exit(10); ?>');

$process = new Process();
$process->setCommand($tmpFile);
$process->start();

do { usleep(500); } while ($process->isRunning());

var_dump($process->getExitCode());
unlink($tmpFile);
?>
--EXPECT--
int(10)