<?php

namespace HRDNS\Socket\Server\WebSocket;

class Request
{

    private $method = 'GET';

    private $path = '/';

    private $query = '';

    private $version = '1.0';

    private $header = [];

    public static function parse(string $string): self
    {
        $lines = explode("\n", trim($string));
        $method = '';
        $path = '';
        $query = '';
        $version = '';
        $header = [];
        foreach ($lines as $line => $str) {
            if (preg_match('/([a-z]{1,}) (.*) HTTP\/([0-9.]{3,})/i', $str, $match)) {
                $method = strtoupper($match[1]);
                $path = (string)$match[2];
                if (strpos($path, '?') !== false) {
                    list($path, $query) = explode('?', $path, 2);
                }
                $version = (string)$match[3];
                continue;
            }
            list($key, $value) = explode(': ', $str, 2);
            $key = trim(strtolower($key));
            $value = trim($value);
            if (!empty($key) && !empty($value)) {
                $header[$key] = (isset($header[$key]) ? $header[$key] . '; ' : '') . $value;
            }
        }
        return new self($method, $path, $query, $version, $header);
    }

    public function __construct(string $method, string $path, string $query, string $version, array $header)
    {
        $this->method = $method;
        $this->path = $path;
        $this->query = $query;
        $this->version = $version;
        $this->header = $header;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getVersion(): string
    {
        return (string)$this->version;
    }

    public function getHeader($field = null)
    {
        if (!$field) {
            return $this->header;
        }
        $field = strtolower($field);
        return isset($this->header[$field]) ? $this->header[$field] : '';
    }

}
