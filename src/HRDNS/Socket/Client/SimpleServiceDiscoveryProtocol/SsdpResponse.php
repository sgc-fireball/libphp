<?php

namespace HRDNS\Socket\Client\SimpleServiceDiscoveryProtocol;

use HRDNS\Types\Struct;

class SsdpResponse extends Struct
{

    public function setFromString(string $string)
    {
        $headers = explode("\n",trim($string));
        unset($headers[0]);

        $data = [];
        array_walk($headers,function(&$line)use(&$data){
            list($key,$value) = explode(':',$line,2);
            $data[strtolower($key)] = trim($value);
        });
        $this->data = $data;
        return $this;
    }

    public function getLocation()
    {
        return isset($this->data['location']) ? $this->data['location'] : null;
    }

    public function getServer()
    {
        return isset($this->data['server']) ? $this->data['server'] : 'unknown device';
    }

}
