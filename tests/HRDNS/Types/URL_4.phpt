--TEST--
Testing HRDNS\Types\URL - test5
--FILE--
<?php
$basePath = preg_replace('/\/tests\/.*/', '', realpath(__DIR__));
require_once($basePath . '/tests/bootstrap.php');

use \HRDNS\Types\URL;

$url = new URL();
$url->setURL('https://test1:test2@test3:1337/test4/test5.html?test6=test7&test8[]=test9#test10');

echo 'Scheme:' . $url->getScheme() . "\n";
echo 'User:' . $url->getUser() . "\n";
echo 'Pass:' . $url->getPassword() . "\n";
echo 'Host:' . $url->getHost() . "\n";
echo 'Port:' . $url->getPort() . "\n";
echo 'Path:' . $url->getPath() . "\n";
echo 'Query:' . $url->getQuery() . "\n";
echo 'Fragment:' . $url->getFragment() . "\n";
echo 'URL:' . $url->getURL() . "\n";

$url->setURL('//test3:8080');

echo 'Scheme:' . $url->getScheme() . "\n";
echo 'User:' . $url->getUser() . "\n";
echo 'Pass:' . $url->getPassword() . "\n";
echo 'Host:' . $url->getHost() . "\n";
echo 'Port:' . $url->getPort() . "\n";
echo 'Path:' . $url->getPath() . "\n";
echo 'Query:' . $url->getQuery() . "\n";
echo 'Fragment:' . $url->getFragment() . "\n";
echo 'URL:' . $url->getURL() . "\n";

?>
--EXPECT--
Scheme:https
User:test1
Pass:test2
Host:test3
Port:1337
Path:/test4/test5.html
Query:test6=test7&test8[]=test9
Fragment:test10
URL:https://test1:test2@test3:1337/test4/test5.html?test6=test7&test8[]=test9#test10
Scheme:https
User:
Pass:
Host:test3
Port:8080
Path:/
Query:
Fragment:
URL:https://test3:8080/