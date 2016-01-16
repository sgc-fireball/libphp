--TEST--
Testing \HRDNS\System\Process\Daemon - Interval
--FILE--
<?php

declare(ticks = 1);
error_reporting(0);

$basePath = preg_replace('/\/tests\/.*/','',realpath(__DIR__));
require_once($basePath.'/vendor/autoload.php');

use HRDNS\System\Process\Daemon;

$daemon = Daemon::getInstance();
if ( $daemon->getProcessName() == 'Daemon' ) {
    $daemon->setProcessName('Daemon_0');
}
printf("ProcessName: %s\n",$daemon->getProcessName());
printf("PidDirPath: %s\n",$daemon->getPidPath());
printf("PidFilePath: %s\n",$daemon->getPidPathname());

printf("IsDaemonRunning: %s\n",$daemon->isDaemonAlreadyRunning()?'true':'false');

$daemon->start();

printf("IsDaemonRunning: %s\n",$daemon->isDaemonAlreadyRunning()?'true':'false');

$daemon->stop();

printf("IsDaemonRunning: %s\n",$daemon->isDaemonAlreadyRunning()?'true':'false');

?>
--EXPECT--
ProcessName: Daemon_0
PidDirPath: /var/run/Daemon_0
PidFilePath: /var/run/Daemon_0/daemon.pid
IsDaemonRunning: false
IsDaemonRunning: true
IsDaemonRunning: false
