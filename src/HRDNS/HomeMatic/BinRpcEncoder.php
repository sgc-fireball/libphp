<?php

namespace HRDNS\HomeMatic;

class BinRpcEncoder
{

    /**
     * @param string $methodName
     * @param array $arguments
     * @return string
     */
    public function encodeRequest(string $methodName, array $arguments = []): string
    {
        $content = '';
        foreach ($arguments as $argument) {
            $content .= $this->encodeData($argument);
        }

        return pack(
            'A3CNNA*NA*',
            BinRpcProtocol::PREFIX,
            BinRpcProtocol::TYPE_REQUEST,
            8 + strlen($methodName) + strlen($content),
            strlen($methodName),
            $methodName,
            count($arguments),
            $content
        );
    }

    /**
     * @param array $data
     * @return string
     */
    public function encodeResponse(array $data): string
    {
        $content = $this->encodeData($data);

        return pack('A3CNA*', BinRpcProtocol::PREFIX, BinRpcProtocol::TYPE_RESPONSE, strlen($content), $content);
    }

    /**
     * @param array $data
     * @return string
     */
    public function encodeError(array $data): string
    {
        $content = $this->encodeData($data);

        return pack('A3CNA*', BinRpcProtocol::PREFIX, BinRpcProtocol::TYPE_ERROR, strlen($content), $content);
    }

    /**
     * @param mixed $data
     * @return string
     * @throws \InvalidArgumentException
     */
    private function encodeData($data): string
    {
        switch (true) {
            case is_bool($data):
                return $this->encodeBool((bool)$data);
            case is_integer($data):
                return $this->encodeInteger((int)$data);
            case is_numeric($data):
                return $this->encodeFloat((float)$data);
            case is_string($data):
                return $this->encodeString((string)$data);
            case is_array($data) && isset($data[0]):
                return $this->encodeArray((array)$data);
            case (is_array($data) && !isset($data[0])) || is_object($data):
                return $this->encodeStruct((array)$data);
        }
        throw new \InvalidArgumentException('Invalid argument 1, unable to convert into binrpc format.');
    }

    /**
     * @see https://de.wikipedia.org/wiki/Einfache_Genauigkeit
     * @see http://zogg-jm.ch/IEEE_754_Umwandlung_Gleitkomma_zu_32_u_64_Bit.html
     * @see https://github.com/openhab/openhab1-addons/blob/db62be70e9cd9561036d8925a177a2e37aaa4bd4/bundles/binding/org.openhab.binding.homematic/src/main/java/org/openhab/binding/homematic/internal/binrpc/BinRpcRequest.java
     * @param float $data
     * @return string
     */
    private function encodeFloat(float $data): string
    {
        $exponent = floor(log(abs($data)) / 0.6931471805599453) + 1;
        $mantissa = floor(($data * pow(2, -$exponent)) * (1 << 30));
        $result = pack('NNN', BinRpcProtocol::TYPE_FLOAT, $mantissa, $exponent);

        return $result;
    }

    /**
     * does not supports negative values!
     *
     * @param integer $data
     * @return string
     * @throws \InvalidArgumentException
     */
    private function encodeInteger(int $data): string
    {
        if ($data < 0 || 2147483647 < $data) {
            throw new \InvalidArgumentException('Homematic binrpc supports only uint32 bit values with.');
        }
        $result = pack('NN', BinRpcProtocol::TYPE_INTEGER, $data);

        return $result;
    }

    /**
     * @param bool $data
     * @return string
     */
    private function encodeBool(bool $data): string
    {
        return pack('NC', BinRpcProtocol::TYPE_BOOL, $data ? 1 : 0);
    }

    /**
     * @param string $data
     * @return string
     */
    private function encodeString(string $data): string
    {
        return pack('NNA*', BinRpcProtocol::TYPE_STRING, strlen($data), $data);
    }

    /**
     * @param array $data
     * @return string
     */
    private function encodeArray(array $data): string
    {
        $length = count($data);
        $result = '';
        $result .= pack('NN', BinRpcProtocol::TYPE_ARRAY, $length);
        for ($i = 0; $i < $length; $i++) {
            $result .= $this->encodeData($data[$i]);
        }

        return $result;
    }

    /**
     * @param array $data
     * @return string
     */
    private function encodeStruct(array $data): string
    {
        $count = count($data);
        $result = '';
        $result .= pack('NN', BinRpcProtocol::TYPE_STRUCT, $count);
        foreach ($data as $key => $value) {
            $result .= pack('NA*', strlen($key), $key);
            $result .= $this->encodeData($value);
        }

        return $result;
    }

}
