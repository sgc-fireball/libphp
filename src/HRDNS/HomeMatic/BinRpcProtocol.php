<?php

namespace HRDNS\HomeMatic;

class BinRpcProtocol
{

    const PREFIX = 'Bin';
    const TYPE_REQUEST = 0;
    const TYPE_RESPONSE = 1;
    const TYPE_ERROR = 255;
    const TYPE_INTEGER = 1;
    const TYPE_BOOL = 2;
    const TYPE_STRING = 3;
    const TYPE_FLOAT = 4;
    const TYPE_ARRAY = 256;
    const TYPE_STRUCT = 257;

    /** @var BinRpcEncoder */
    private $encoder = null;

    /** @var BinRpcDecoder */
    private $decoder = null;

    public function __construct()
    {
        $this->encoder = new BinRpcEncoder();
        $this->decoder = new BinRpcDecoder();
    }

    /**
     * @param string $methodName
     * @param array $data
     * @return string
     */
    public function encodeRequest(string $methodName, array $data = []): string
    {
        return $this->encoder->encodeRequest($methodName, $data);
    }

    /**
     * @param array $data
     * @return string
     */
    public function encodeResponse(array $data): string
    {
        return $this->encoder->encodeResponse($data);
    }

    /**
     * @param int $code
     * @param string $message
     * @return string
     */
    public function encodeError(int $code = -1, string $message = 'Unknown error.'): string
    {
        return $this->encoder->encodeError(['faultCode' => $code, 'faultString' => $message]);
    }

    /**
     * @param string $data
     * @return array
     */
    public function decode(string $data): array
    {
        return $this->decoder->decode($data);
    }

    /**
     * @param string $data
     * @return array
     */
    public function decodeRequest(string $data): array
    {
        return $this->decoder->decodeRequest($data);
    }

    /**
     * @param string $data
     * @return array
     */
    public function decodeResponse(string $data): array
    {
        return $this->decoder->decodeResponse($data);
    }

}
