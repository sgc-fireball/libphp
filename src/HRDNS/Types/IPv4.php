<?php

namespace HRDNS\Types;

class IPv4
{

    /** @var string * */
    private $ipAddr = '0.0.0.0';

    /** @var int */
    private $cidr = 0;

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
        if (!filter_var($ipAddr, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            throw new \InvalidArgumentException();
        }
        $this->ipAddr = $ipAddr;
        return $this;
    }

    /**
     * @return string
     */
    public function getIp(): string
    {
        return $this->ipAddr;
    }

    /**
     * @param int $long
     * @return IPv4
     * @throws \InvalidArgumentException
     */
    public function setLong(int $long): self
    {
        if ($long < 0 || $long > pow(2, 32)) {
            throw new \InvalidArgumentException();
        }
        $this->ipAddr = ip2long($long);
        return $this;
    }

    /**
     * @return int
     */
    public function getLong(): int
    {
        return ip2long($this->ipAddr);
    }

    /**
     * @return string
     */
    public function getInArpa(): string
    {
        return sprintf('%s.in-addr.arpa',implode('.',array_reverse(explode('.',$this->ipAddr))));
    }

    /**
     * @param int $cidr
     * @return self
     * @throws \InvalidArgumentException
     */
    public function setCIDR(int $cidr): self
    {
        if ($cidr < 0 || $cidr > 32) {
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
        if (!filter_var($ipAddr, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            throw new \InvalidArgumentException();
        }
        $bin = decbin(ip2long($ipAddr));
        if (!preg_match('/^(1{0,32})(0{0,32})$/', $bin)) {
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
        return long2ip(((1 << 32) - 1) << (32 - $this->cidr));
    }

    /**
     * @return string
     */
    public function getNetmask(): string
    {
        $bin = str_pad(substr(decbin(ip2long($this->ipAddr)), 0, $this->cidr), 32, '0', STR_PAD_RIGHT);
        return long2ip(bindec($bin));
    }

    /**
     * @return string
     */
    public function getBroadcast(): string
    {
        $bin = str_pad(substr(decbin(ip2long($this->ipAddr)), 0, $this->cidr), 32, '1', STR_PAD_RIGHT);
        return long2ip(bindec($bin));
    }

    /**
     * @return string
     */
    public function getIpv6(): string
    {
        list($block1, $block2, $block3, $block4) = explode('.', $this->ipAddr);
        return strtoupper(
            sprintf(
                '2002:%s:%s:%s:%s::',
                str_pad(dechex($block1), 4, '0', STR_PAD_LEFT),
                str_pad(dechex($block2), 4, '0', STR_PAD_LEFT),
                str_pad(dechex($block3), 4, '0', STR_PAD_LEFT),
                str_pad(dechex($block4), 4, '0', STR_PAD_LEFT)
            )
        );
    }

    /**
     * @param string|self $ipAddr
     * @return bool
     * @throws \InvalidArgumentException
     */
    public function isIpInSubnet($ipAddr): bool
    {
        $ipAddr = $ipAddr instanceof self ? $ipAddr->getIp() : $ipAddr;
        if (!filter_var($ipAddr, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            throw new \InvalidArgumentException();
        }
        $min = ip2long($this->getNetmask());
        $ipAddr = ip2long($ipAddr);
        $max = ip2long($this->getBroadcast());
        return $min <= $ipAddr && $ipAddr <= $max;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return sprintf('%s/%d', $this->getIp(), $this->getCIDR());
    }

}
