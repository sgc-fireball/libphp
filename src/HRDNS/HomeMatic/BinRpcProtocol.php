<?php

namespace HRDNS\HomeMatic;

class BinRpcProtocol
{

    const PREFIX = 'Bin';
    const TYPE_REQUEST = 0x0000;
    const TYPE_RESPONSE = 0x0001;
    const TYPE_INTEGER = 0x0001;
    const TYPE_BOOL = 0x0002;
    const TYPE_STRING = 0x0003;
    const TYPE_FLOAT = 0x0004;
    const TYPE_STRUCT = 0x0101;
    const TYPE_ARRAY = 0x0100;

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

    /**
     * @param string $data
     * @return string
     * @todo remove debug function
     */
    public static function debugStrToHex(string $data): string
    {
        $result = '';
        foreach (preg_split('//', $data) as $char) {
            $hex = dechex(ord($char));
            $result .= strlen($hex) == 2 ? $hex : '0' . $hex;
        }

        return $result;
    }

}
