<?php

namespace HRDNS\HomeMatic;

class BinRpcDecoder
{

    /**
     * @param string $data
     * @return array
     * @throws \InvalidArgumentException
     */
    public function decode(string $data): array
    {
        if (substr($data, 0, 3) !== BinRpcProtocol::PREFIX) {
            throw new \InvalidArgumentException('Argument 1 is not an homematic binrpc string.', 1);
        }

        $packet = unpack('A3prefix/Ctype', $data);
        if ($packet['prefix'] !== BinRpcProtocol::PREFIX) {
            throw new \InvalidArgumentException('Argument 1 is not an homematic binrpc string.', 2);
        }
        if ($packet['type'] === BinRpcProtocol::TYPE_REQUEST) {
            return $this->decodeRequest($data);
        }
        if ($packet['type'] === BinRpcProtocol::TYPE_RESPONSE) {
            return $this->decodeResponse($data);
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
        if (substr($data, 0, 3) !== BinRpcProtocol::PREFIX) {
            throw new \InvalidArgumentException('Argument 1 is not an homematic binrpc string.', 4);
        }

        $format = 'A3prefix/Ctype/NmsgSize/NmethodSize';
        $packet = unpack($format, $data);
        if ($packet['prefix'] !== BinRpcProtocol::PREFIX) {
            throw new \InvalidArgumentException('Argument 1 is not an homematic binrpc string.', 5);
        }
        if ($packet['type'] !== BinRpcProtocol::TYPE_REQUEST) {
            throw new \InvalidArgumentException('Argument 1 is not an homematic binrpc request.', 6);
        }
        $format .= '/A' . $packet['methodSize'] . 'methodName';
        $packet = unpack($format, $data);

        $data = substr($data, 3 + 1 + 4 + 4 + $packet['methodSize'] + 4);

        return [
            'type' => 'request',
            'method' => $packet['methodName'],
            'params' => $this->decodeData($data)
        ];
    }

    /**
     * @param string $data
     * @return array
     * @throws \InvalidArgumentException
     */
    public function decodeResponse(string $data): array
    {
        if (substr($data, 0, 3) !== BinRpcProtocol::PREFIX) {
            throw new \InvalidArgumentException('Argument 1 is not an homematic binrpc string.', 7);
        }

        $format = 'A3prefix/Ctype/NmsgSize/A*data';
        $packet = unpack($format, $data);
        if ($packet['prefix'] !== BinRpcProtocol::PREFIX) {
            throw new \InvalidArgumentException('Argument 1 is not an homematic binrpc string.', 8);
        }
        if ($packet['type'] !== BinRpcProtocol::TYPE_RESPONSE) {
            throw new \InvalidArgumentException('Argument 1 is not an homematic binrpc response.', 9);
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
     * @throws \InvalidArgumentException
     */
    private function decodeData(string &$data)
    {
        $info = unpack('Ntype', $data);
        switch ($info['type']) {
            case BinRpcProtocol::TYPE_INTEGER:
                return $this->decodeInteger($data);
            case BinRpcProtocol::TYPE_BOOL:
                return $this->decodeBool($data);
            case BinRpcProtocol::TYPE_STRING:
                return $this->decodeString($data);
            case BinRpcProtocol::TYPE_FLOAT:
                return $this->decodeFloat($data);
            case BinRpcProtocol::TYPE_STRUCT:
                return $this->decodeStruct($data);
            case BinRpcProtocol::TYPE_ARRAY:
                return $this->decodeArray($data);
        }
        throw new \InvalidArgumentException('Invalid argument 1, unable to convert from binrpc format.', 10);
    }

    /**
     * @param string $data
     * @return float
     */
    private function decodeFloat(string &$data): float
    {
        $info = unpack('Ntype/Nmantissa/Nexponent', $data);
        $result = round((pow(2, $info['exponent'])) * ($info['mantissa'] / (1 << 30)), 6);
        $data = substr($data, 4 + 4 + 4);

        return $result;
    }

    /**
     * @param string $data
     * @return integer
     */
    private function decodeInteger(string &$data): int
    {
        $info = @unpack('Ntype/Nvalue', $data);
        $result = (int)$info['value'];
        $data = substr($data, 4 + 4);

        return $result;
    }

    /**
     * @param string $data
     * @return boolean
     */
    private function decodeBool(string &$data): bool
    {
        $info = unpack('Ntype/Cvalue', $data);
        $result = (bool)$info['value'];
        $data = substr($data, 4 + 1);

        return $result;
    }

    /**
     * @param string $data
     * @return string
     */
    private function decodeString(string &$data): string
    {
        $info = unpack('Ntype/Nsize', $data);
        $info = unpack('Ntype/Nsize/A' . $info['size'] . 'content', $data);
        $result = (string)$info['content'];
        $data = substr($data, 4 + 4 + $info['size']);

        return $result;
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
