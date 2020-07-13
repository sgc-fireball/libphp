--TEST--
Testing HRDNS\System\FileSystem\TSFile - write
--FILE--
<?php declare(strict_types=1);
$basePath = preg_replace('/\/tests\/.*/', '', realpath(__DIR__));
require_once($basePath . '/tests/bootstrap.php');

use HRDNS\System\FileSystem\TSFile;

$pathname = tempnam(sys_get_temp_dir(), 'phpunit');
$file = new TSFile($pathname,'w');
$file->write('test1234');
echo file_get_contents($file->getPathname());
$file->unlink();
?>
--EXPECT--
test1234