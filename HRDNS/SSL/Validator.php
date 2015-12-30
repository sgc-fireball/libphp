<?php

namespace HRDNS\SSL;

class Validator
{

    protected $executeAble = null;

    public function __construct()
    {
        $this->executeAble = trim(shell_exec('which openssl'));
        if (empty($this->executeAble) || !file_exists($this->executeAble) || !is_executable($this->executeAble)) {
            throw new \Exception('Could not found openssl executeable.');
        }
    }

    protected $protocols = array(
        'ssl2',
        'ssl3',
        'tls1',
        'tls1_1',
        'tls1_2',
    );

    public function getCiphers()
    {
        return explode(':', shell_exec(sprintf('%s ciphers', $this->executeAble)));
    }

    public function verify($host, $port = 443)
    {
        if ($port < 1 || 65535 < $port || !$host) {
            return false;
        }
        $result = array(
            'host' => $host,
            'port' => $port,
            'protocol' => array(),
            'results' => array()
        );
        foreach ($this->getCiphers() as $cipher) {
            $cipher = trim($cipher);
            foreach ($this->protocols as $protocol) {
                $protocol = trim($protocol);
                $name = strtoupper(
                    sprintf(
                        '%s_%s',
                        $protocol,
                        $cipher
                    )
                );
                $cmd = sprintf(
                    '%s s_client "-%s" -cipher "%s" -connect "%s:%d" < /dev/null 3>/dev/null 2>/dev/null > /dev/null',
                    $this->executeAble,
                    $protocol,
                    $cipher,
                    $host,
                    $port
                );
                system($cmd, $return);
                $return = (int) !((bool)$return);
                if (!isset($result['protocol'][$protocol])) {
                    $result['protocol'][$protocol] = 0;
                }
                if ($return) {
                    $result['protocol'][$protocol]++;
                }
                $result['results'][$name] = $return;
            }
        }
        ksort($result['results']);

        return $result;
    }

}
