<?php

namespace HRDNS\Socket\Server\WebSocket;

class Frame
{

    const TYPE_TEXT = 1;
    const TYPE_BINARY = 2;
    const TYPE_CLOSE = 8;
    const TYPE_PING = 9;
    const TYPE_PONG = 10;

    /** @var bool */
    private $incomplete = false;

    /** @var bool */
    private $fin = false;

    /** @var bool */
    private $rsv1 = false;

    /** @var bool */
    private $rsv2 = false;

    /** @var bool */
    private $rsv3 = false;

    /** @var bool */
    private $opcode = false;

    /** @var bool */
    private $mask = false;

    /** @var bool */
    private $length = false;

    /** @var string */
    private $body = '';

    /** @var string */
    private $masking = '';

    /**
     * @static
     * @param string $buffer
     * @return self
     */
    public static function decode(string $buffer)
    {
        $data = unpack('C2header', $buffer);
        $frame = [
            'incomplete' => false,
            'fin' => (bool)(($data['header1'] & 0b10000000) >> 7),
            'rsv1' => (bool)(($data['header1'] & 0b10000000) >> 6),
            'rsv2' => (bool)(($data['header1'] & 0b10000000) >> 5),
            'rsv3' => (bool)(($data['header1'] & 0b10000000) >> 4),
            'opcode' => $data['header1'] & 0b00001111,
            'mask' => (bool)(($data['header2'] & 0b10000000) >> 7),
            'length' => $data['header2'] & 0b01111111,
            'body' => null,
            'masking' => null,
        ];

        if ($frame['length'] == 126) {
            $frame = array_merge($data, unpack('x2/nlength/a4masking/a*body', $buffer));
        } else {
            if ($frame['length'] == 127) {
                $frame = array_merge($frame, unpack('x2/n4length/a4masking/a*body', $buffer));
                $data['length'] = $data['length1'] . $data['length2'] . $data['length3'] . $data['length4'];
                unset($data['length1'], $data['length2'], $data['length3'], $data['length4']);
            } else {
                $frame = array_merge($frame, unpack('x2/a4masking/a*body', $buffer));
            }
        }

        $length = strlen($frame['body']);
        for ($i = 0, $key = $length ; $i < $key ; ++$i) {
            $frame['body'][$i] = $frame['body'][$i] ^ $frame['masking'][$i % 4];
        }
        if (strlen($frame['body']) < $frame['length']) {
            $frame['incomplete'] = true;
        } else {
            $frame['body'] = substr($frame['body'], 0, $frame['length']);
        }
        return new self($frame);
    }

    /**
     * @param array $frame
     */
    public function __construct(array $frame = [])
    {
        $this->incomplete = (bool)(isset($frame['incomplete']) ? $frame['incomplete'] : false);
        $this->fin = (bool)(isset($frame['fin']) ? $frame['fin'] : false);
        $this->rsv1 = (bool)(isset($frame['rsv1']) ? $frame['rsv1'] : false);
        $this->rsv2 = (bool)(isset($frame['rsv2']) ? $frame['rsv2'] : false);
        $this->rsv3 = (bool)(isset($frame['rsv3']) ? $frame['rsv3'] : false);
        $this->opcode = (int)(isset($frame['opcode']) ? $frame['opcode'] : 1);
        $this->mask = (bool)(isset($frame['mask']) ? $frame['mask'] : false);
        $this->masking = (string)(isset($frame['masking']) ? $frame['masking'] : '');
        $this->length = (int)(isset($frame['length']) ? $frame['length'] : 0);
        $this->body = (string)(isset($frame['body']) ? $frame['body'] : '');
        if ($this->mask && empty($this->masking)) {
            $this->masking = chr(mt_rand(1, 255)) . chr(mt_rand(1, 255)) . chr(mt_rand(1, 255)) . chr(mt_rand(1, 255));
        }
    }

    /**
     * @return string
     */
    public function encode(): string
    {
        $opcode = $this->opcode == self::TYPE_TEXT ? 129 : 0;
        $opcode = $this->opcode == self::TYPE_CLOSE ? 136 : $opcode;
        $opcode = $this->opcode == self::TYPE_PING ? 137 : $opcode;
        $opcode = $this->opcode == self::TYPE_PONG ? 138 : $opcode;
        $frame = chr($opcode);

        $length = strlen($this->body);
        if ($length > 65535) {
            $binLength = str_split(sprintf('%064b', $length), 8);
            $frame .= chr($this->mask ? 255 : 127);
            for ($i = 0 ; $i < 8 ; $i++) {
                $frame .= chr(bindec($binLength[$i]));
            }
        } else {
            if ($length > 125) {
                $binLength = str_split(sprintf('%016b', $length), 8);
                $frame .= chr($this->mask ? 255 : 127);
                $frame .= chr(bindec($binLength[0]));
                $frame .= chr(bindec($binLength[1]));
            } else {
                $frame .= chr($this->mask ? $length + 128 : $length);
            }
        }

        $masking = str_split($this->masking);
        if ($this->mask) {
            $frame .= $masking;
        }

        for ($i = 0 ; $i < $length ; $i++) {
            $frame .= $this->mask ? $this->body[$i] ^ $masking[$i % 4] : $this->body[$i];
        }

        return $frame;
    }

    /**
     * @return boolean
     */
    public function isIncomplete(): bool
    {
        return (bool)$this->incomplete;
    }

    /**
     * @return string
     */
    public function getBody(): string
    {
        return (string)$this->body;
    }

    /**
     * @return boolean
     */
    public function isMasked(): bool
    {
        return $this->mask;
    }

    /**
     * @return integer
     */
    public function getOpCode(): int
    {
        return $this->opcode;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getBody();
    }

}
