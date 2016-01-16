--TEST--
Testing HRDNS\SSL\Validator - verify
--FILE--
<?php
$basePath = preg_replace('/\/tests\/.*/','',realpath(__DIR__));
require_once($basePath.'/vendor/autoload.php');

use HRDNS\SSL\Validator;

$validator = new Validator();
$result = $validator->verify('www.hrdns.de',443);

echo 'Host: '.$result['host']."\n";
echo 'Port: '.$result['port']."\n";
echo "Protocol:\n";
echo 'ssl2 = '.(isset($result['protocol']['ssl2'])&&$result['protocol']['ssl2']>0?'true':'false')."\n";
echo 'ssl3 = '.(isset($result['protocol']['ssl3'])&&$result['protocol']['ssl3']>0?'true':'false')."\n";
echo 'tls1 = '.(isset($result['protocol']['tls1'])&&$result['protocol']['tls1']>0?'true':'false')."\n";
echo 'tls1_1 = '.(isset($result['protocol']['tls1_1'])&&$result['protocol']['tls1_1']>0?'true':'false')."\n";
echo 'tls1_2 = '.(isset($result['protocol']['tls1_2'])&&$result['protocol']['tls1_2']>0?'true':'false')."\n";
?>
--EXPECT--
Host: www.hrdns.de
Port: 443
Protocol:
ssl2 = false
ssl3 = false
tls1 = false
tls1_1 = false
tls1_2 = true
