<?php declare(strict_types=1);

namespace HRDNS\SSL;

class Validator
{

    /** @var string */
    protected $executeAble = '';

    /**
     * @var array
     */
    protected $protocols = array(
        'ssl2',
        'ssl3',
        'tls1',
        'tls1_1',
        'tls1_2',
    );

    /**
     * @throws \RuntimeException
     */
    public function __construct()
    {
        $this->executeAble = trim(shell_exec('which openssl'));
        if (empty($this->executeAble) || !file_exists($this->executeAble) || !is_executable($this->executeAble)) {
            throw new \RuntimeException('Could not found openssl executeable.');
        }
    }

    /**
     * @return array
     */
    public function getProtocols(): array
    {
        return $this->protocols;
    }

    /**
     * @return array
     */
    public function getCiphers(): array
    {
        static $ciphers;
        if (!isset($ciphers)) {
            $ciphers = explode(
                ':',
                shell_exec(
                    sprintf(
                        '%s ciphers',
                        $this->executeAble
                    )
                )
            );
            array_walk(
                $ciphers,
                function (&$value) {
                    $value = trim($value);
                }
            );
        }
        return $ciphers;
    }

    /**
     * @param string $host
     * @param integer $port
     * @return array
     * @throws \InvalidArgumentException
     */
    public function verify(string $host, int $port): array
    {
        $result = array(
            'host' => $host,
            'port' => $port,
            'protocol' => [],
            'results' => []
        );

        foreach ($this->getCiphers() as $cipher) {
            foreach ($this->getProtocols() as $protocol) {
                $name = strtoupper(
                    sprintf(
                        '%s_%s',
                        $protocol,
                        $cipher
                    )
                );
                if (!isset($result['protocol'][$protocol])) {
                    $result['protocol'][$protocol] = 0;
                }
                $return = $this->verifySingle(
                    $host,
                    $port,
                    $protocol,
                    $cipher
                );
                if ($return) {
                    $result['protocol'][$protocol]++;
                }
                $result['results'][$name] = (int)$return;
            }
        }
        ksort($result['results']);
        return $result;
    }

    /**
     * @param string $host
     * @param integer $port
     * @param string $protocol
     * @param string $cipher
     * @return boolean
     * @throws \InvalidArgumentException
     */
    public function verifySingle(string $host, int $port, string $protocol, string $cipher): bool
    {
        if ($port < 1 || 65535 < $port || !$host) {
            throw new \InvalidArgumentException('Invalid port number.');
        }
        if (!in_array($protocol, $this->getProtocols())) {
            throw new \InvalidArgumentException('Invalid protocol nane.');
        }
        if (!in_array($cipher, $this->getCiphers())) {
            throw new \InvalidArgumentException('Invalid cipher name.');
        }

        $cmd = sprintf(
            '%s s_client "-%s" -cipher "%s" -connect "%s:%d" < /dev/null 3>/dev/null 2>/dev/null > /dev/null',
            $this->executeAble,
            $protocol,
            $cipher,
            $host,
            $port
        );
        system($cmd, $return);
        return !((bool)$return);
    }

}
