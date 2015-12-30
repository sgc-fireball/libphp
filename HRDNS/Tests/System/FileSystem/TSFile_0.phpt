--TEST--
Testing HRDNS\System\FileSystem\TSFile - write
--FILE--
<?php
require_once(__DIR__ . '/../../../../vendor/autoload.php');
use HRDNS\System\FileSystem\TSFile;
$pathname = tempnam(sys_get_temp_dir(),'phpunit');
$file = new TSFile($pathname);
$file->write('test1234');
echo file_get_contents($file->getPathname());
$file->unlink();
?>
--EXPECT--
test1234