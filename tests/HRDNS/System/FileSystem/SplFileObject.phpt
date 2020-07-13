--TEST--
Testing \SplFileObject - write
--FILE--
<?php declare(strict_types=1);
$pathname = tempnam(sys_get_temp_dir(), 'phpunit');
$file = new \SplFileObject($pathname, 'a+');
$file->fwrite('test1234');
echo file_get_contents($pathname);
unlink($file->getPathname());
?>
--EXPECT--
test1234