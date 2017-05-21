<?php

namespace HRDNS\Socket\Client\SimpleServiceDiscoveryProtocol;

use HRDNS\Types\Struct;

class SsdpResponse extends Struct
{

    public function __construct(array $data = [])
    {
        parent::__construct($data);
        $this->data['httpversion'] = '1.0';
        $this->data['httpcode'] = '200';
        $this->data['httpstatus'] = 'OK';
        $this->data['location'] = isset($this->data['location']) ? $this->data['location'] : '';
        $this->data['server'] = isset($this->data['server']) ? $this->data['server'] : '';
        $this->data['usn'] = isset($this->data['usn']) ? $this->data['usn'] : '';
    }

    /**
     * @param string $string
     * @return self
     */
    public function setFromString(string $string)
    {
        $headers = explode("\n", trim($string));
        if (!preg_match('/^HTTP\/(\d\.\d) ([\d]{3}) (.*)$/', $headers[0], $matches)) {
            $this->data['httpcode'] = 403;
            if (!preg_match('/^NOTIFY \* HTTP\/(\d\.\d)/', $headers[0], $matches)) {
                return $this;
            }
            $this->data['httpversion'] = $matches[1];
            $this->data['httpcode'] = 200;
        } else {
            $this->data['httpversion'] = $matches[1];
            $this->data['httpcode'] = $matches[2];
            $this->data['httpstatus'] = $matches[3];
        }
        unset($headers[0]);

        $data = [];
        array_walk(
            $headers,
            function (&$line) use (&$data) {
                list($key, $value) = explode(':', $line, 2);
                $data[strtolower($key)] = trim($value);
            }
        );
        $this->data = array_merge($this->data, $data);
        return $this;
    }

    /**
     * @return string
     */
    public function getHttpVersion(): string
    {
        return $this->data['httpversion'];
    }

    /**
     * @return integer
     */
    public function getHttpCode(): int
    {
        return $this->data['httpcode'];
    }

    /**
     * @return string
     */
    public function getHttpStatus(): string
    {
        return $this->data['httpstatus'];
    }

    /**
     * @return string
     */
    public function getLocation(): string
    {
        $this->data['location'] = isset($this->data['location']) ? $this->data['location'] : '';
        return $this->data['location'];
    }

    /**
     * @return string
     */
    public function getServer(): string
    {
        $this->data['server'] = isset($this->data['server']) ? $this->data['server'] : '';
        return $this->data['server'];
    }

    /**
     * @return string
     */
    public function getUSN(): string
    {
        $this->data['usn'] = isset($this->data['usn']) ? $this->data['usn'] : '';
        return $this->data['usn'];
    }

}
