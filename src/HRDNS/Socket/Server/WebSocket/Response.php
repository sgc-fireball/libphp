<?php

namespace HRDNS\Socket\Server\WebSocket;

class Response
{

    private $version = '1.0';

    private $body = '';

    private $code = 200;

    private $headers = [];

    public static function createFromRequest(Request $request)
    {
        $response = new Response();
        $response->setVersion($request->getVersion());
        return $response;
    }

    public function __construct(string $body = '', int $code = 200)
    {
        $this->setBody($body);
        $this->setCode($code);
    }

    public function setVersion(string $version): self
    {
        $this->version = $version;
        return $this;
    }

    public function setBody(string $body): self
    {
        $this->body = $body;
        return $this;
    }

    public function setCode(int $code): self
    {
        $this->code = $code;
        return $this;
    }

    public function addHeader(string $key, string $value): self
    {
        $this->headers[$key] = $value;
        return $this;
    }

    public function __toString(): string
    {
        $response = sprintf("HTTP/%s %s %s\n", $this->version, $this->code, $this->getStatusByCode($this->code));
        foreach ($this->headers as $key => $value) {
            $response .= sprintf("%s: %s\n", $key, $value);
        }
        $response .= sprintf("Content-Length: %d\n", strlen($this->body));
        $response .= "\n";
        $response .= $this->body;
        return $response;
    }

    private function getStatusByCode(int $code): string
    {
        $codes = [
            100 => 'Continue',
            101 => 'Switching Protocols',
            102 => 'Processing',
            200 => 'OK',
            201 => 'Created',
            202 => 'Accepted',
            203 => 'Non-Authoritative Information',
            204 => 'No Content',
            205 => 'Reset Content',
            206 => 'Partial Content',
            207 => 'Multi-Status',
            208 => 'Already Reported',
            226 => 'IM Used',
            300 => 'Multiple Choices',
            301 => 'Moved Permanently',
            302 => 'Found',
            303 => 'See Other',
            304 => 'Not Modified',
            305 => 'Use Proxy',
            306 => 'Switch Proxy',
            307 => 'Temporary Redirect',
            308 => 'Permanent Redirect',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            402 => 'Payment Required',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            406 => 'Not Acceptable',
            407 => 'Proxy Authentication Required',
            408 => 'Request Time-out',
            409 => 'Conflict',
            410 => 'Gone',
            411 => 'Length Required',
            412 => 'Precondition Failed',
            413 => 'Request Entity Too Large',
            414 => 'Request-URL Too Long',
            415 => 'Unsupported Media Type',
            416 => 'Requested range not satisfiable',
            417 => 'Expectation Failed',
            418 => 'I�m a teapot',
            420 => 'Policy Not Fulfilled',
            421 => 'Misdirected Request',
            422 => 'Unprocessable Entity',
            423 => 'Locked',
            424 => 'Failed Dependency',
            425 => 'Unordered Collection',
            426 => 'Upgrade Required',
            428 => 'Precondition Required',
            429 => 'Too Many Requests',
            431 => 'Request Header Fields Too Large',
            451 => 'Unavailable For Legal Reasons',
            444 => 'No Response',
            449 => 'The request should be retried after doing the appropriate action',
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Time-out',
            505 => 'HTTP Version not supported',
            506 => 'Variant Also Negotiates',
            507 => 'Insufficient Storage',
            508 => 'Loop Detected',
            509 => 'Bandwidth Limit Exceeded',
            510 => 'Not Extended',
            511 => 'Network Authentication Required'
        ];
        return isset($codes[$code]) ? $codes[$code] : 'Unknown';
    }

}
