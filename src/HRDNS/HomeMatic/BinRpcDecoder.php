<?php

namespace HRDNS\HomeMatic;

use HRDNS\Exception\HomeMaticIOException;

class BinRpcDecoder
{

    /**
     * @param string $data
     * @return array
     * @throws \InvalidArgumentException
     */
    public function decode(string $data): array
    {
        if (strlen($data) < 4) {
            throw new \InvalidArgumentException('Argument 1 is to short for a homematic binrpc packet. (1)', 1);
        }
        $packet = unpack('A3prefix/Ctype', $data);
        if ($packet['prefix'] !== BinRpcProtocol::PREFIX) {
            throw new \InvalidArgumentException('Argument 1 is not a homematic binrpc string.', 2);
        }
        if ($packet['type'] === BinRpcProtocol::TYPE_REQUEST) {
            return $this->decodeRequest($data);
        }
        if ($packet['type'] === BinRpcProtocol::TYPE_RESPONSE) {
            return $this->decodeResponse($data);
        }
        if ($packet['type'] === BinRpcProtocol::TYPE_ERROR) {
            $error = $this->decodeError($data);
            throw new HomeMaticIOException($error['params']['faultString'], (int)$error['params']['faultCode']);
        }
        throw new \InvalidArgumentException('Argument 1 is an invalid homematic binrpc message type.', 3);
    }

    /**
     * @param string $data
     * @return array
     * @throws \InvalidArgumentException
     */
    public function decodeRequest(string $data): array
    {
        if (strlen($data) < 12) {
            throw new \InvalidArgumentException('Argument 1 is to short for a homematic binrpc packet. (2)', 4);
        }
        $format = 'A3prefix/Ctype/NmsgSize/NmethodSize';
        $packet = unpack($format, $data);
        if ($packet['prefix'] !== BinRpcProtocol::PREFIX) {
            throw new \InvalidArgumentException('Argument 1 is not a homematic binrpc string.', 5);
        }
        if ($packet['type'] !== BinRpcProtocol::TYPE_REQUEST) {
            throw new \InvalidArgumentException('Argument 1 is not a homematic binrpc request.', 6);
        }
        $format .= '/A' . $packet['methodSize'] . 'methodName/Ncount';
        $packet = unpack($format, $data);

        if (strlen($data)-8 != $packet['msgSize']) {
            throw new HomeMaticIOException('Not enough data.',HomeMaticIOException::ERROR_PARSER_NOT_ENOUGH_INPUT);
        }

        $data = substr($data, 3 + 1 + 4 + 4 + $packet['methodSize'] + 4);

        $params = [];
        for ($i = 0; $i < $packet['count']; $i++) {
            $params[] = $this->decodeData($data);
        }

        return [
            'type' => 'request',
            'method' => $packet['methodName'],
            'params' => $params,
        ];
    }

    /**
     * @param string $data
     * @return array
     * @throws \InvalidArgumentException
     */
    public function decodeResponse(string $data): array
    {
        if (strlen($data) < 8) {
            throw new \InvalidArgumentException('Argument 1 is to short for a homematic binrpc packet. (3)', 7);
        }
        $format = 'A3prefix/Ctype/NmsgSize/A*data';
        $packet = unpack($format, $data);
        if ($packet['prefix'] !== BinRpcProtocol::PREFIX) {
            throw new \InvalidArgumentException('Argument 1 is not a homematic binrpc string.', 8);
        }
        if ($packet['type'] !== BinRpcProtocol::TYPE_RESPONSE && $packet['type'] !== BinRpcProtocol::TYPE_ERROR) {
            throw new \InvalidArgumentException('Argument 1 is not a homematic binrpc response.', 9);
        }

        if (strlen($data)-8 != $packet['msgSize']) {
            throw new HomeMaticIOException('Not enough data.',HomeMaticIOException::ERROR_PARSER_NOT_ENOUGH_INPUT);
        }

        $data = substr($data, 3 + 1 + 4);

        return [
            'type' => 'response',
            'method' => 'unknown',
            'params' => $this->decodeData($data)
        ];
    }

    /**
     * @param string $data
     * @return mixed
     */
    public function decodeError(string $data): array
    {
        $result = $this->decodeResponse($data);
        $result['type'] = 'error';

        return $result;
    }

    /**
     * @param string $data
     * @return mixed
     * @throws \InvalidArgumentException
     */
    private function decodeData(string &$data)
    {
        if (!strlen($data)) {
            throw new \InvalidArgumentException('Argument 1 could not be empty.', 10);
        }
        $info = unpack('Ntype', $data);
        if (is_array($info) && array_key_exists('type', $info)) {
            switch ($info['type']) {
                case BinRpcProtocol::TYPE_INTEGER:
                    return $this->decodeInteger($data);
                case BinRpcProtocol::TYPE_BOOL:
                    return $this->decodeBool($data);
                case BinRpcProtocol::TYPE_STRING:
                    return $this->decodeString($data);
                case BinRpcProtocol::TYPE_FLOAT:
                    return $this->decodeFloat($data);
                case BinRpcProtocol::TYPE_ARRAY:
                    return $this->decodeArray($data);
                case BinRpcProtocol::TYPE_STRUCT:
                    return $this->decodeStruct($data);
                default:
                    throw new \InvalidArgumentException('Invalid type ' . $info['type'] . ' found in binrpc request.',
                        11);
            }
        }
        throw new \InvalidArgumentException('Invalid argument 1. unable to convert from binrpc format.', 12);
    }

    /**
     * @param string $data
     * @return float
     */
    private function decodeFloat(string &$data): float
    {
        $info = unpack('Ntype/Nmantissa/Nexponent', $data);
        $data = substr($data, 4 + 4 + 4);

        return round((pow(2, $info['exponent'])) * ($info['mantissa'] / (1 << 30)),6);
    }

    /**
     * @param string $data
     * @return integer
     */
    private function decodeInteger(string &$data): int
    {
        $info = unpack('Ntype/Nvalue', $data);
        $data = substr($data, 4 + 4);

        return (int)$info['value'];
    }

    /**
     * @param string $data
     * @return boolean
     */
    private function decodeBool(string &$data): bool
    {
        $info = unpack('Ntype/Cvalue', $data);
        $data = substr($data, 4 + 1);

        return (bool)$info['value'];
    }

    /**
     * @param string $data
     * @return string
     */
    private function decodeString(string &$data): string
    {
        $info = unpack('Ntype/Nsize', $data);
        $info = unpack('Ntype/Nsize/A' . $info['size'] . 'content', $data);
        $data = substr($data, 4 + 4 + $info['size']);

        return (string)$info['content'];
    }

    /**
     * @param string $data
     * @return array
     */
    private function decodeArray(string &$data): array
    {
        $result = [];
        $info = unpack('Ntype/Ncount', $data);
        $data = substr($data, 4 + 4);
        for ($i = 0; $i < $info['count']; $i++) {
            $result[] = $this->decodeData($data);
        }

        return $result;
    }

    /**
     * @param string $data
     * @return array
     */
    private function decodeStruct(string &$data): array
    {
        $result = [];
        $info = unpack('Ntype/Ncount', $data);
        $data = substr($data, 4 + 4);
        for ($i = 0; $i < $info['count']; $i++) {
            $item = unpack('Nsize', $data);
            $item = unpack('Nsize/A' . $item['size'] . 'key', $data);
            $key = $item['key'];
            $data = substr($data, 4 + $item['size']);
            $result[$key] = $this->decodeData($data);
        }

        return $result;
    }

}
