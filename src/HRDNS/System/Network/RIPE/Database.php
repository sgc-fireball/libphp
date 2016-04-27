<?php

namespace HRDNS\System\Network\RIPE;

use HRDNS\Protocol\FTP;
use HRDNS\Types\CSV;
use HRDNS\Types\URL;
use HRDNS\Exception\IOException;

class Database
{

    protected $url = 'ftp://anonymous:anonymous@ftp.ripe.net/ripe/stats/%Y/delegated-ripencc-extended-%Y%m%d.bz2';

    /**
     * @throws \RuntimeException
     */
    public function __construct()
    {
        if (!function_exists('bzdecompress')) {
            throw new \RuntimeException('Missing php module bzip.');
        }
    }

    /**
     * @param string $target
     * @return Database
     * @throws IOException
     */
    public function download(string $target): self
    {
        $date = new \DateTime('yesterday');

        $url = new URL(
            preg_replace(
                array(
                    '/%Y/',
                    '/%m/',
                    '/%d/'
                ),
                array(
                    $date->format('Y'),
                    $date->format('m'),
                    $date->format('d')
                ),
                $this->url
            )
        );

        (new FTP())
            ->setTimeout(3)
            ->setHost($url->getHost())
            ->setPort($url->getPort())
            ->setUser($url->getUser())
            ->setPassword($url->getPassword())
            ->connect()
            ->login()
            ->passiv()
            ->get($url->getPath(), $target)
            ->disconnect();

        return $this;
    }

    /**
     * @param string $file
     * @return Database
     * @throws IOException
     * @throws \RuntimeException
     */
    public function decompress(string $file): self
    {
        if (($content = file_get_contents($file)) === false) {
            throw new IOException('Could not read file: ' . $file);
        }
        if (($content = bzdecompress($content)) === false) {
            throw new \RuntimeException('Could not decompress data from file: ' . $file);
        }
        if (file_put_contents($file, $content) === false) {
            throw new IOException('Could not write data to file: ' . $file);
        }
        return $this;
    }

    /**
     * @param string $file
     * @return Database
     */
    public function convert(string $file): self
    {
        $csv = new CSV($file, '|');
        $csv->open();
        foreach ($csv as $id => $line) {
            if ($id < 4) {
                continue;
            }
            switch (strtolower($line[2])) {
                case 'ipv4':
                    $this->handleIpv4($line);
                    break;
                case 'ipv6':
                    $this->handleIpv6($line);
                    break;
                case 'asn':
                    $this->handleAsn($line);
                    break;
            }
        }
        $csv->close();
        return $this;
    }

    public function handleIpv4(array $line): self
    {
        /**
         * [0] => ripencc
         * [1] => FR
         * [2] => ipv4
         * [3] => 2.0.0.0
         * [4] => 1048576
         * [5] => 20100712
         * [6] => allocated
         * [7] => a1e33a7d-5964-4bd7-ae72-980c57b0cf72
         */
        $countryCode = isset($line[1]) ? $line[1] : '*';
        $network = isset($line[3]) ? $line[3] : '255.255.255.255';
        $cidr = (int)(32 - log($line[4]) / log(2));
        $since = isset($line[5]) ? $line[5] : date('Ymd');
        $status = isset($line[6]) ? $line[6] : 'unknown';
        $uuid = isset($line[7]) ? $line[7] : '';

        if (in_array($status, ['reserved', 'available'])) {
            return $this;
        }

        printf("IPv4 :: %s/%d :: %s :: %s\n", $network, $cidr, $status, $countryCode);
        return $this;
    }

    public function handleIpv6(array $line): self
    {
        /**
         * [0] => ripencc
         * [1] => NL
         * [2] => ipv6
         * [3] => 2001:67c:26ac::
         * [4] => 48
         * [5] => 20120203
         * [6] => assigned
         * [7] => 39530da4-b33b-4077-a077-06bf85f3f17e
         */
        $countryCode = isset($line[1]) ? $line[1] : '*';
        $network = isset($line[3]) ? $line[3] : 'ffff:ffff:ffff:ffff:ffff:ffff:ffff:ffff';
        $since = isset($line[5]) ? $line[5] : date('Ymd');
        $status = isset($line[6]) ? $line[6] : 'unknown';
        $uuid = isset($line[7]) ? $line[7] : '';
        $cidr = isset($line[4]) ? $line[4] : '128';

        if (in_array($status, ['reserved', 'available'])) {
            return $this;
        }

        printf("IPv6 :: %s/%d :: %s :: %s\n", $network, $cidr, $status, $countryCode);
        return $this;
    }

    public function handleAsn(array $line): self
    {
        /**
         * [0] => ripencc
         * [1] => DE
         * [2] => asn
         * [3] => 28
         * [4] => 1
         * [5] => 19930901
         * [6] => allocated
         * [7] => 2053ae4a-520c-44ab-bbdb-c4e751e3c4f6
         */
        $countryCode = isset($line[1]) ? $line[1] : '*';
        $asnNumber = isset($line[3]) ? $line[3] : '0';
        $since = isset($line[5]) ? $line[5] : date('Ymd');
        $status = isset($line[6]) ? $line[6] : 'unknown';
        $uuid = isset($line[7]) ? $line[7] : '';

        if (in_array($status, ['reserved', 'available'])) {
            return $this;
        }

        printf("ASN%d :: %s :: \n", $asnNumber, $status, $countryCode);
        return $this;
    }

}
