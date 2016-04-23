<?php

namespace HRDNS\Types;

class IPv6
{

    /** @var string * */
    private $ipAddr = '::1';

    /** @var int */
    private $cidr = 64;

    /**
     * @param string|null $ipAddr
     * @param int|null $cidr
     */
    public function __construct(string $ipAddr = null, int $cidr = null)
    {
        $ipAddr ? $this->setIp($ipAddr) : null;
        $cidr ? $this->setCIDR($cidr) : null;
    }

    /**
     * @param string $ipAddr
     * @return self
     * @throws \InvalidArgumentException
     */
    public function setIp(string $ipAddr): self
    {
        if (!filter_var($ipAddr, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            throw new \InvalidArgumentException();
        }
        $this->ipAddr = self::compress($ipAddr);
        return $this;
    }

    /**
     * @return string
     */
    public function getIp(): string
    {
        return self::compress($this->ipAddr);
    }

    /**
     * @return string An int128.
     * @throws \RuntimeException
     */
    public function getLong(): string
    {
        if (!function_exists('gmp_init') || !function_exists('gmp_strval')) {
            throw new \RuntimeException('');
        }
        return gmp_strval(gmp_init(str_replace(':', '', self::uncompress($this->ipAddr)), 16), 10);
    }

    /**
     * @param string $long An int128.
     * @return self
     * @throws \RuntimeException
     */
    public function setLong($long): self
    {
        if (!function_exists('gmp_init') || !function_exists('gmp_strval')) {
            throw new \RuntimeException('');
        }
        $ipAddr = str_pad(gmp_strval(gmp_init($long, 10), 16), '0', 32, STR_PAD_LEFT);
        $ipAddr = self::compress(substr(preg_replace('/([A-f0-9]{4})/', '$1:', $ipAddr), 0, -1));
        $this->setIp($ipAddr);
        return $this;
    }

    /**
     * @return string
     */
    public function getInArpa(): string
    {
        $ipAddr = self::uncompress($this->ipAddr);
        $ipAddr = str_replace(':', '', $ipAddr);
        $ipAddr = str_split($ipAddr);
        $ipAddr = array_reverse($ipAddr);
        $ipAddr = implode('.', $ipAddr);
        return sprintf('%s.ip6.arpa', $ipAddr);
    }

    /**
     * @param int $cidr
     * @return self
     * @throws \InvalidArgumentException
     */
    public function setCIDR(int $cidr): self
    {
        if ($cidr < 0 || $cidr > 128) {
            throw new \InvalidArgumentException();
        }
        $this->cidr = $cidr;
        return $this;
    }

    /**
     * @return int
     */
    public function getCIDR(): int
    {
        return $this->cidr;
    }

    /**
     * @param string $ipAddr
     * @return self
     * @throws \InvalidArgumentException
     */
    public function setSubnetmask(string $ipAddr): self
    {
        if (!filter_var($ipAddr, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            throw new \InvalidArgumentException();
        }
        $bin = $this->ip2bin($ipAddr);
        if (!preg_match('/^(1{0,128})(0{0,128})$/', $bin)) {
            throw new \InvalidArgumentException();
        }
        $this->setCIDR(strlen(trim($bin, '0')));
        return $this;
    }

    /**
     * @return string
     */
    public function getSubnetmask(): string
    {
        $bin = str_pad('', $this->cidr, '1', STR_PAD_LEFT);
        $bin = str_pad($bin, 128, '0', STR_PAD_RIGHT);
        return $this->bin2ip($bin);
    }

    /**
     * @return string
     */
    public function getNetmask(): string
    {
        $bin = $this->ip2bin($this->ipAddr);
        $bin = str_pad(substr($bin, 0, $this->cidr), 128, '0', STR_PAD_RIGHT);
        return $this->bin2ip($bin);
    }

    /**
     * @return string
     */
    public function getBroadcast(): string
    {
        $bin = $this->ip2bin($this->ipAddr);
        $bin = str_pad(substr($bin, 0, $this->cidr), 128, '1', STR_PAD_RIGHT);
        return $this->bin2ip($bin);
    }

    /**
     * @param string|self $ipAddr
     * @return bool
     * @throws \InvalidArgumentException
     */
    public function isIpInSubnet($ipAddr): bool
    {
        $ipAddr = $ipAddr instanceof self ? $ipAddr->getIp() : $ipAddr;
        if (!filter_var($ipAddr, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            throw new \InvalidArgumentException();
        }
        $netmask = $this->ip2bin($this->getNetmask());
        $regex = sprintf(
            '/^%s/',
            substr($netmask, 0, $this->cidr)
        );
        $ipAddr = $this->ip2bin($ipAddr);
        return (bool)preg_match($regex, $ipAddr);
    }

    /**
     * @static
     * @param string $ipAddr
     * @return string
     */
    public static function compress(string $ipAddr): string
    {
        $ipAddr = self::uncompress($ipAddr);
        $ipAddr = preg_replace('/(^|:)0+([0-9])/', '\1\2', $ipAddr);
        if (preg_match_all('/(?:^|:)(?:0(?::|$))+/', $ipAddr, $matches, PREG_OFFSET_CAPTURE)) {
            $max = 0;
            $position = null;
            foreach ($matches[0] as $block) {
                if (strlen($block[0]) > $max) {
                    $max = strlen($block[0]);
                    $position = $block[1];
                }
            }
            $ipAddr = substr_replace($ipAddr, '::', $position, $max);
        }
        $ipAddr = explode(':', $ipAddr);
        array_walk(
            $ipAddr,
            function (&$value) {
                if ($value != '') {
                    $value = ltrim($value, 0);
                    $value = empty($value) ? 0 : $value;
                }
            }
        );
        return implode(':', $ipAddr);
    }

    /**
     * @static
     * @param string $ip
     * @return string
     */
    public static function uncompress(string $ip): string
    {
        $uncompressIpAddr = $ip;
        if (strpos($ip, '::') !== false) {
            list($ip1, $ipAddr2) = explode('::', $ip);
            if ($ip1 == '') {
                $count1 = -1;
            } else {
                if (0 < ($position = substr_count($ip1, ':'))) {
                    $count1 = $position;
                } else {
                    $count1 = 0;
                }
            }
            if ($ipAddr2 == '') {
                $count2 = -1;
            } else {
                if (0 < ($position = substr_count($ipAddr2, ':'))) {
                    $count2 = $position;
                } else {
                    $count2 = 0;
                }
            }
            if (strstr($ipAddr2, '.')) {
                $count2++;
            }
            /**
             *  ::
             */
            if ($count1 == -1 && $count2 == -1) {
                $uncompressIpAddr = '0:0:0:0:0:0:0:0';
            } else {
                /**
                 * ::xxx
                 */
                if ($count1 == -1) {
                    $fill = str_repeat('0:', 7 - $count2);
                    $uncompressIpAddr = str_replace('::', $fill, $uncompressIpAddr);
                } else {
                    /**
                     * xxx::
                     */
                    if ($count2 == -1) {
                        $fill = str_repeat(':0', 7 - $count1);
                        $uncompressIpAddr = str_replace('::', $fill, $uncompressIpAddr);
                    } else {
                        /**
                         * xxx::xxx
                         */
                        $fill = str_repeat(':0:', 6 - $count2 - $count1);
                        $uncompressIpAddr = str_replace('::', $fill, $uncompressIpAddr);
                        $uncompressIpAddr = str_replace('::', ':', $uncompressIpAddr);
                    }
                }
            }
        }
        $uncompressIpAddr = explode(':', $uncompressIpAddr);
        array_walk(
            $uncompressIpAddr,
            function (&$value) {
                $value = str_pad($value, 4, '0', STR_PAD_LEFT);
            }
        );
        return implode(':', $uncompressIpAddr);
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return sprintf('%s/%d', $this->getIp(), $this->getCIDR());
    }

    /**
     * @param string $ipAddr
     * @return string
     */
    private function ip2bin(string $ipAddr): string
    {
        $bin = '';
        foreach (explode(':', self::uncompress($ipAddr)) as $block) {
            $bin .= str_pad((string)base_convert($block, 16, 2), 16, '0', STR_PAD_LEFT);
        }
        return $bin;
    }

    /**
     * @param string $bin
     * @return string
     */
    private function bin2ip(string $bin): string
    {
        $hex = '';
        foreach (str_split($bin, 4) as $block) {
            $hex .= base_convert($block, 2, 16);
        }
        return self::compress(substr(preg_replace('/([A-f0-9]{4})/', '$1:', $hex), 0, -1));
    }

}
