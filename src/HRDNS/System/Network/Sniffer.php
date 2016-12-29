<?php

namespace HRDNS\System\Network;

use HRDNS\Core\EventHandler;

if (!defined('SO_BINDTODEVICE')) {
    define('SO_BINDTODEVICE', 25);
}

if (!defined('SOL_ICMP')) {
    define('SOL_ICMP', 1);
}

/**
 * @see http://www.binarytides.com/code-a-packet-sniffer-in-php/
 */
class Sniffer
{

    /** @var resource|null */
    private $tcpSocket = null;

    /** @var EventHandler */
    private $eventHandler;

    /** @var array */
    private $ipHeader = [];

    /** @var array */
    private $tcpHeader = [];

    /** @var array */
    private $udpHeader = [];

    /** @var array */
    private $icmpHeader = [];

    /** @var boolean */
    private $terminated = false;

    /** @var null|callable */
    private $callback = null;

    /**
     * @param callable $callable
     * @param string $device
     * @throws \InvalidArgumentException
     */
    public function __construct(callable $callable, string $device = 'lo')
    {
        $this->resetPacketDefinition();
        $this->eventHandler = EventHandler::get();
        $this->icmpSocket = socket_create(AF_INET, SOCK_RAW, SOL_ICMP);
        $this->tcpSocket = socket_create(AF_INET, SOCK_RAW, SOL_TCP);
        $this->udpSocket = socket_create(AF_INET, SOCK_RAW, SOL_UDP);
        socket_set_option($this->icmpSocket, SOL_SOCKET, SO_BROADCAST, 1);
        socket_set_option($this->tcpSocket, SOL_SOCKET, SO_BROADCAST, 1);
        socket_set_option($this->udpSocket, SOL_SOCKET, SO_BROADCAST, 1);
        socket_set_option($this->icmpSocket, SOL_SOCKET, SO_BINDTODEVICE, $device);
        socket_set_option($this->tcpSocket, SOL_SOCKET, SO_BINDTODEVICE, $device);
        socket_set_option($this->udpSocket, SOL_SOCKET, SO_BINDTODEVICE, $device);
        socket_set_nonblock($this->icmpSocket);
        socket_set_nonblock($this->tcpSocket);
        socket_set_nonblock($this->udpSocket);
        if (!is_callable($callable)) {
            throw new \InvalidArgumentException('Invalid callback function.');
        }
        $this->callback = $callable;
    }

    /**
     * @return $this
     */
    public function setTerminated()
    {
        $this->terminated = true;
        return $this;
    }

    /**
     * @param int $limit
     * @return null
     */
    public function listen(int $limit = -1)
    {
        while (!$this->terminated && ($limit > 0 || $limit == -1)) {
            $limit = $limit == -1 ? -1 : $limit - 1;
            socket_recv($this->tcpSocket, $buffer, 65536, 0);
            if ($buffer && $packet = $this->parsePacket($buffer)) {
                call_user_func($this->callback, $packet);
            }
            socket_recv($this->udpSocket, $buffer, 65536, 0);
            if ($buffer && $packet = $this->parsePacket($buffer)) {
                call_user_func($this->callback, $packet);
            }
            socket_recv($this->icmpSocket, $buffer, 65536, 0);
            if ($buffer && $packet = $this->parsePacket($buffer)) {
                call_user_func($this->callback, $packet);
            }
        }
        return null;
    }

    public function __destruct()
    {
        if ($this->tcpSocket) {
            @socket_close($this->tcpSocket);
        }
        if ($this->udpSocket) {
            @socket_close($this->udpSocket);
        }
    }

    private function resetPacketDefinition()
    {
        $ipHeader = [];
        $ipHeader[] = 'CipVersionIHL'; // 4bit version, 4bit byte ihl
        $ipHeader[] = 'CipTOS'; // 1 bytes tos
        $ipHeader[] = 'nipLength'; // 2 bytes length
        $ipHeader[] = 'nipIdentification'; // 2 bytes id
        $ipHeader[] = 'nipFlagsFragmentOffset'; // 2 bytes (3 bits ip flags / 13 bits fragment offset)
        $ipHeader[] = 'CipTTL'; // 1 byte ttl
        $ipHeader[] = 'CipProtocol'; // 1 byte ipProtocol
        $ipHeader[] = 'nipChecksum'; // 2 bytes checksum
        $ipHeader[] = 'NipSource'; // 4 bytes source
        $ipHeader[] = 'NipDestination'; // 4 bytes destination
        // ipOptions%d: optional Nx 4 bytes options
        $this->ipHeader = implode('/', $ipHeader);

        $tcpHeader = [];
        $tcpHeader[] = 'ntcpSourcePort'; // 2 bytes source port
        $tcpHeader[] = 'ntcpDestinationPort'; // 2 bytes destination port
        $tcpHeader[] = 'NtcpSequenceNumber'; // 4 bytes sequence number
        $tcpHeader[] = 'NtcpAcknowledgementNumber'; // 4 bytes acknowledgement number
        $tcpHeader[] = 'CtcpOffsetReserved'; // 4bit offset, 4bit reserved
        $tcpHeader[] = 'CtcpFlags'; // 1 byte flags
        $tcpHeader[] = 'ntcpWindowSize'; // 2 bytes window size
        $tcpHeader[] = 'ntcpChecksum'; // 2 bytes checksum
        $tcpHeader[] = 'ntcpUrgentPointer'; // 2 bytes urgent pointer
        // tcpOptions%d: optional Nx 4 bytes options
        $this->tcpHeader = implode('/', $tcpHeader);

        $udpHeader = [];
        $udpHeader[] = 'nudpSourcePort'; // 2 bytes source port
        $udpHeader[] = 'nudpDestinationPort'; // 2 bytes destination port
        $udpHeader[] = 'nudpLength'; // 2 bytes length
        $udpHeader[] = 'nudpChecksum'; // 2 bytes checksum
        $udpHeader[] = 'H*data'; // 4 bytes n times
        $this->udpHeader = implode('/', $udpHeader);

        $icmpHeader = [];
        $icmpHeader[] = 'CicmpType'; // 1 byte type
        $icmpHeader[] = 'CicmpCode'; // 1 byte code
        $icmpHeader[] = 'nicmpChecksum'; // 2 bytes checksum
        $icmpHeader[] = 'NicmpInformation'; // 4 bytes other message secific informations
        $icmpHeader[] = 'H*data'; // 4 bytes n times
        $this->icmpHeader = implode('/', $icmpHeader);
    }

    /**
     * @see https://foren6.files.wordpress.com/2011/04/ip-header-v41.png
     * @param string $buffer
     * @return array|null
     */
    public function parsePacket(string $buffer)
    {
        $this->resetPacketDefinition();

        $packet = unpack($this->ipHeader, $buffer);
        $packet['ipIHL'] = $packet['ipVersionIHL'] & 0x0F;
        if ($packet['ipIHL'] > 5) {
            $count = $packet['ipIHL'] - 5;
            for ($i = 1 ; $i <= $count ; $i++) {
                $this->ipHeader .= sprintf('/NipOption%d', $i); // 4 bytes options
            }
        }

        switch ($packet['ipProtocol']) {
            case 1:
                $packet = $this->parseIcmpPacket($buffer);
                break;
            case 6:
                $packet = $this->parseTcpPacket($buffer);
                break;
            case 17:
                $packet = $this->parseUdpPacket($buffer);
                break;
            default:
                $packet = unpack($this->ipHeader, $buffer);
                break;
        }

        $packet['ipVersion'] = $packet['ipVersionIHL'] >> 4;
        $packet['ipIHL'] = $packet['ipVersionIHL'] & 0x0F;
        unset($packet['ipVersionIHL']);

        $packet['ipFlags'] = $packet['ipFlagsFragmentOffset'] >> 13;
        $packet['ipFragmentOffset'] = $packet['ipFlagsFragmentOffset'] & 0x1FFF;
        unset($packet['ipFlagsFragmentOffset']);

        $packet['ipFlags'] = [
            'x' => $packet['ipFlags'] >> 2, // reserved
            'd' => ($packet['ipFlags'] >> 1) & 0x01, // do not fragment
            'm' => $packet['ipFlags'] & 0x01, // more fragments follow
        ];

        $packet['ipTOS'] = [
            'precedence' => $packet['ipTOS'] & 0xE0, // Prioritaet
            'delay' => $packet['ipTOS'] & 0x10, // Verzögerung
            'throughput' => $packet['ipTOS'] & 0x08, // Durchsatz
            'reliability' => $packet['ipTOS'] & 0x04, // Zuverlaessigkeit
            'reserved' => $packet['ipTOS'] & 0x03 // Reserviert
        ];

        $packet['ipSource'] = long2ip($packet['ipSource']);
        $packet['ipDestination'] = long2ip($packet['ipDestination']);
        return $packet;
    }

    /**
     * @param string $buffer
     * @return array|null
     */
    public function parseIcmpPacket(string $buffer)
    {
        $packet = unpack($this->ipHeader . '/' . $this->icmpHeader, $buffer);
        $data = isset($packet['data']) ? $packet['data'] : '';
        $packet['data'] = '';
        for ($i = 0; $i < strlen($data); $i = $i += 2) {
            $packet['data'] .= chr(
                hexdec(
                    $data[$i] . $data[$i + 1]
                )
            );
        }
        return $packet;
    }

    /**
     * @see https://nmap.org/book/images/hdr/MJB-UDP-Header-800x264.png
     * @param string $buffer
     * @return array|null
     */
    public function parseUdpPacket(string $buffer)
    {
        $packet = unpack($this->ipHeader . '/' . $this->udpHeader, $buffer);
        $data = isset($packet['data']) ? $packet['data'] : '';
        $packet['data'] = '';
        for ($i = 0; $i < strlen($data); $i = $i += 2) {
            $packet['data'] .= chr(
                hexdec(
                    $data[$i] . $data[$i + 1]
                )
            );
        }
        return $packet;
    }

    /**
     * @see https://nmap.org/book/images/hdr/MJB-TCP-Header-800x564.png
     * @param string $buffer
     * @return array|null
     */
    public function parseTcpPacket(string $buffer)
    {
        $packet = unpack($this->ipHeader . '/' . $this->tcpHeader, $buffer);
        $packet['tcpOffset'] = $packet['tcpOffsetReserved'] >> 4;

        if ($packet['tcpOffset'] > 5) {
            $count = $packet['tcpOffset'] - 5;
            for ($i = 1 ; $i <= $count ; $i++) {
                $this->tcpHeader .= sprintf('/NtcpOption%d', $i);
            }
        }

        $this->tcpHeader .= '/H*data';
        $packet = unpack($this->ipHeader . '/' . $this->tcpHeader, $buffer);
        $packet['tcpOffset'] = $packet['tcpOffsetReserved'] >> 4;
        $packet['tcpReserved'] = $packet['tcpOffsetReserved'] & 0x0F;
        unset($packet['tcpOffsetReserved']);

        $packet['tcpFlags'] = [
            'cwr' => ($packet['tcpFlags'] >> 7) & 0x01,
            'ece' => ($packet['tcpFlags'] >> 6) & 0x01,
            'urg' => ($packet['tcpFlags'] >> 5) & 0x01, // dringend
            'ack' => ($packet['tcpFlags'] >> 4) & 0x01, // angenommen
            'psh' => ($packet['tcpFlags'] >> 3) & 0x01, // push
            'rst' => ($packet['tcpFlags'] >> 2) & 0x01, // reset
            'syn' => ($packet['tcpFlags'] >> 1) & 0x01, // sync
            'fin' => $packet['tcpFlags'] & 0x01 // finish
        ];

        $data = isset($packet['data']) ? $packet['data'] : '';
        $packet['data'] = '';
        for ($i = 0; $i < strlen($data); $i = $i += 2) {
            $packet['data'] .= chr(
                hexdec(
                    $data[$i] . $data[$i + 1]
                )
            );
        }

        return $packet;
    }

}
