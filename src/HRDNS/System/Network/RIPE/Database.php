<?php

namespace HRDNS\System\Network\RIPE;

use HRDNS\Protocol\FTP;
use HRDNS\Types\CSV;
use HRDNS\Types\IPv4;
use HRDNS\Types\IPv6;
use HRDNS\Types\URL;
use HRDNS\Exception\IOException;

class Database
{

    /**
     * @see ftp://ftp.ripe.net/ripe/stats/RIR-Statistics-Exchange-Format.txt
     * @var string
     */
    private $url = 'ftp://anonymous:anonymous@ftp.ripe.net/ripe/stats/%Y/delegated-ripencc-extended-%Y%m%d.bz2';

    /** @var callable */
    private $callbackIpv4 = null;

    /** @var callable */
    private $callbackIpv6 = null;

    /** @var callable */
    private $callbackAsn = null;

    /**
     * @param callable $callbackIpv4
     * @param callable $callbackIpv6
     * @param callable $callbackAsn
     * @throws \RuntimeException
     */
    public function __construct(callable $callbackIpv4, callable $callbackIpv6, callable $callbackAsn)
    {
        if (!function_exists('bzdecompress')) {
            throw new \RuntimeException('Missing php module bzip.');
        }
        $this->callbackIpv4 = $callbackIpv4;
        $this->callbackIpv6 = $callbackIpv6;
        $this->callbackAsn = $callbackAsn;
    }

    /**
     * @param string $target
     * @return Database
     * @throws IOException
     */
    public function download(string $target)
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
    public function decompress(string $file)
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
    public function convert(string $file)
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

    public function handleIpv4(array $line)
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
        if (!$this->callbackIpv4) {
            return $this;
        }
        $ip = new IPv4($line[3], (int)(32 - log($line[4]) / log(2)));
        $callbackIpv4 = $this->callbackIpv4;
        $callbackIpv4($ip, $line[1], (int)$line[5], $line);
        return $this;
    }

    public function handleIpv6(array $line)
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
        if (!$this->callbackIpv6) {
            return $this;
        }
        $ip = new IPv6($line[3], $line[4]);
        $callbackIpv6 = $this->callbackIpv6;
        $callbackIpv6($ip, $line[1], (int)$line[5], $line);
        return $this;
    }

    public function handleAsn(array $line)
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
        if (!$this->callbackAsn) {
            return $this;
        }
        $callbackAsn = $this->callbackAsn;
        $callbackAsn('AS' . $line[3], $line[1], (int)$line[5], $line);
        return $this;
    }

}
