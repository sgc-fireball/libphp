--TEST--
Testing HRDNS\System\FileSystem\File - write
--FILE--
<?php
$basePath = preg_replace('/\/tests\/.*/','',__DIR__);
require_once($basePath.'/vendor/autoload.php');

use HRDNS\System\FileSystem\File;

$pathname = tempnam(sys_get_temp_dir(),'phpunit');
$file = new File($pathname);
$file->write('test1234');
echo file_get_contents($file->getPathname());
$file->unlink();
?>
--EXPECT--
test1234