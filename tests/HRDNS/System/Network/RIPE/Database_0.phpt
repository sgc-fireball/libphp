--TEST--
Testing HRDNS\System\Network\RIPE\Database - Test download/decompress/convert
--FILE--
<?php declare(strict_types=1);
$basePath = preg_replace('/\/tests\/.*/', '', realpath(__DIR__));
require_once($basePath . '/tests/bootstrap.php');

use HRDNS\System\Network\RIPE\Database;

$file = tempnam('/tmp','test');
file_put_contents($file,"\n\n\n\n"); // fake file header
file_put_contents($file,implode('|',['ripencc', 'FR', 'ipv4', '2.0.0.0', '1048576', '20100712', 'allocated', 'a1e33a7d-5964-4bd7-ae72-980c57b0cf72'])."\n",FILE_APPEND);
file_put_contents($file,implode('|',['ripencc', 'NL', 'ipv6', '2001:67c:26ac::', '48', '20120203', 'assigned', '39530da4-b33b-4077-a077-06bf85f3f17e'])."\n",FILE_APPEND);
file_put_contents($file,implode('|',['ripencc', 'DE', 'asn', '28', '1', '19930901', 'allocated', '2053ae4a-520c-44ab-bbdb-c4e751e3c4f6'])."\n",FILE_APPEND);

$db = new Database(
    function(\HRDNS\Types\IPv4 $ipv4,string $countyCode,int $since,array $line){
        printf("%s/%s : %s : %d\n",$ipv4->getIp(),$ipv4->getCIDR(),$countyCode,$since);
    },
    function(\HRDNS\Types\IPv6 $ipv6,string $countyCode,int $since,array $line){
        printf("%s/%s : %s : %d\n",$ipv6->getIp(),$ipv6->getCIDR(),$countyCode,$since);
    },
    function(string $asn,string $countyCode,int $since,array $line){
        printf("%s : %s : %d\n",$asn,$countyCode,$since);
    }
);
$db->convert($file);
unlink($file);

?>
--EXPECT--
2.0.0.0/12 : FR : 20100712
2001:67c:26ac::/48 : NL : 20120203
AS28 : DE : 19930901