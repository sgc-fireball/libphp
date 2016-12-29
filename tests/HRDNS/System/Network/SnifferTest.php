<?php

namespace Test\HRDNS\General;

use HRDNS\System\Network\Sniffer;

class SnifferTest extends \PHPUnit_Framework_TestCase
{

    /** @var Sniffer */
    private $sniffer;

    public function setUp()
    {
        $mockBuilder = $this->getMockBuilder(Sniffer::class);
        $mockBuilder->disableOriginalConstructor();
        $mockBuilder->setMethods(['__destruct']);
        $this->sniffer = $mockBuilder->getMock();
    }

    public function testPhpUnit()
    {
        $this->assertInstanceOf(Sniffer::class, $this->sniffer);
    }

    public function testIpHeader()
    {
        $rawPacket = $this->buildRawPacket([
            // ip header
            ['C', 0b11111000], // 1 byte (4 bit version / 4 bit header length)
            ['C', 0b10000000], // 1 byte tos
            ['n', 0b1000000000000000], // 2 bytes length
            ['n', 0b1000000000000000], // 2 bytes id
            ['n', 0b1001000000000000], // 2 bytes (3 bit ip flags / 13 bits fragment offset)
            ['C', 0b10000000], // 1 byte ttl
            ['C', 0b10000000], // 1 byte ip protocol
            ['n', 0b1000000000000000], // 2 byte checksum
            ['N', 0b11101110111011101110111011101110], // 4 byte source ip
            ['N', 0b11101110111011101110111011101110], // 4 byte destination ip
            ['N', 0b01111111111111111111111111111111], // 4 bytes option 1 (if ihl > 5)
            ['N', 0b01111111111111111111111111111111], // 4 bytes option 2 (if ihl > 5)
            ['N', 0b01111111111111111111111111111111], // 4 bytes option 3 (if ihl > 5)
        ]);
        $packet = $this->sniffer->parsePacket($rawPacket);
        $this->assertTrue(is_array($packet));
        $this->assertEquals(15, $packet['ipVersion']);
        $this->assertEquals(8, $packet['ipIHL']);
        $this->assertTrue(array_key_exists('ipTOS', $packet));
        $this->assertTrue(is_array($packet['ipTOS']));
        $this->assertTrue(array_key_exists('precedence', $packet['ipTOS']));
        $this->assertEquals(128, $packet['ipTOS']['precedence']);
        $this->assertTrue(array_key_exists('delay', $packet['ipTOS']));
        $this->assertEquals(0, $packet['ipTOS']['delay']);
        $this->assertTrue(array_key_exists('throughput', $packet['ipTOS']));
        $this->assertEquals(0, $packet['ipTOS']['throughput']);
        $this->assertTrue(array_key_exists('reliability', $packet['ipTOS']));
        $this->assertEquals(0, $packet['ipTOS']['reliability']);
        $this->assertTrue(array_key_exists('reserved', $packet['ipTOS']));
        $this->assertEquals(0, $packet['ipTOS']['reserved']);
        $this->assertEquals(32768, $packet['ipLength']);
        $this->assertEquals(32768, $packet['ipIdentification']);
        $this->assertTrue(array_key_exists('ipFlags', $packet));
        $this->assertTrue(is_array($packet['ipFlags']));
        $this->assertTrue(array_key_exists('x', $packet['ipFlags']));
        $this->assertEquals(1, $packet['ipFlags']['x']);
        $this->assertTrue(array_key_exists('d', $packet['ipFlags']));
        $this->assertEquals(0, $packet['ipFlags']['d']);
        $this->assertTrue(array_key_exists('m', $packet['ipFlags']));
        $this->assertEquals(0, $packet['ipFlags']['m']);
        $this->assertEquals(4096, $packet['ipFragmentOffset']);
        $this->assertEquals(128, $packet['ipTTL']);
        $this->assertEquals(128, $packet['ipProtocol']);
        $this->assertEquals(32768, $packet['ipChecksum']);
        $this->assertEquals('238.238.238.238', $packet['ipSource']);
        $this->assertEquals('238.238.238.238', $packet['ipDestination']);
        $this->assertTrue(array_key_exists('ipOption1', $packet));
        $this->assertEquals(2147483647, $packet['ipOption1']);
        $this->assertTrue(array_key_exists('ipOption2', $packet));
        $this->assertEquals(2147483647, $packet['ipOption2']);
        $this->assertTrue(array_key_exists('ipOption3', $packet));
        $this->assertEquals(2147483647, $packet['ipOption3']);
        $this->assertFalse(array_key_exists('ipOption4', $packet));
    }

    public function testTcpPacket()
    {
        $rawPacket = $this->buildRawPacket([
            // ip header
            ['C', 0b100000110], // 1 byte (4 bit version / 4 bit header length)
            ['C', 0b10000000], // 1 byte tos
            ['n', 0b1000000000000000], // 2 bytes length
            ['n', 0b1000000000000000], // 2 bytes id
            ['n', 0b1001000000000000], // 2 bytes (3 bit ip flags / 13 bits fragment offset)
            ['C', 0b10000000], // 1 byte ttl
            ['C', 0b00000110], // 1 byte ip protocol
            ['n', 0b1000000000000000], // 2 byte checksum
            ['N', 0b11101110111011101110111011101110], // 4 byte source ip
            ['N', 0b11101110111011101110111011101110], // 4 byte destination ip
            ['N', 0b01111111111111111111111111111111], // 4 bytes option 1 (if ihl > 5)
            // tcp header
            ['n', 0b1000000000000000], // 2 bytes source port
            ['n', 0b1000000000000000], // 2 bytes destination port
            ['N', 0b01111111111111111111111111111111], // 4 bytes sequence number
            ['N', 0b01111111111111111111111111111111], // 4 bytes acknowledgement number
            ['C', 0b01101111], // 4bit offset, 4bit reserved
            ['C', 0b10000000], // 1 byte flags
            ['n', 0b1000000000000000], // 2 bytes window size
            ['n', 0b1000000000000000], // 2 bytes checksum
            ['n', 0b1000000000000000], // 2 bytes urgent pointer
            ['N', 0b01111111111111111111111111111111], // 4 bytes option 1 (if offset > 5)
            // tcp data
            ['H*', 'test']
        ]);
        $packet = $this->sniffer->parsePacket($rawPacket);
        $this->assertTrue(is_array($packet));
        $this->assertTrue(array_key_exists('ipOption1', $packet));
        $this->assertEquals(2147483647, $packet['ipOption1']);
        $this->assertEquals(32768, $packet['tcpSourcePort']);
        $this->assertEquals(32768, $packet['tcpDestinationPort']);
        $this->assertEquals(2147483647, $packet['tcpSequenceNumber']);
        $this->assertEquals(2147483647, $packet['tcpAcknowledgementNumber']);
        $this->assertEquals(6, $packet['tcpOffset']); // TDOD
        $this->assertEquals(15, $packet['tcpReserved']);
        $this->assertTrue(array_key_exists('tcpFlags', $packet));
        $this->assertTrue(is_array($packet['tcpFlags']));
        $this->assertEquals(1, $packet['tcpFlags']['cwr']);
        $this->assertEquals(0, $packet['tcpFlags']['ece']);
        $this->assertEquals(0, $packet['tcpFlags']['urg']);
        $this->assertEquals(0, $packet['tcpFlags']['ack']);
        $this->assertEquals(0, $packet['tcpFlags']['psh']);
        $this->assertEquals(0, $packet['tcpFlags']['rst']);
        $this->assertEquals(0, $packet['tcpFlags']['syn']);
        $this->assertEquals(0, $packet['tcpFlags']['fin']);
        $this->assertEquals(32768, $packet['tcpWindowSize']);
        $this->assertEquals(32768, $packet['tcpChecksum']);
        $this->assertEquals(32768, $packet['tcpUrgentPointer']);
        $this->assertEquals(2147483647, $packet['tcpOption1']);
        $this->assertFalse(array_key_exists('tcpOption2', $packet));
        $this->assertEquals('test', $packet['data']);
    }

    public function testUdpPacket()
    {
        $rawPacket = $this->buildRawPacket([
            // ip header
            ['C', 0b100000110], // 1 byte (4 bit version / 4 bit header length)
            ['C', 0b10000000], // 1 byte tos
            ['n', 0b1000000000000000], // 2 bytes length
            ['n', 0b1000000000000000], // 2 bytes id
            ['n', 0b1001000000000000], // 2 bytes (3 bit ip flags / 13 bits fragment offset)
            ['C', 0b10000000], // 1 byte ttl
            ['C', 0b00010001], // 1 byte ip protocol
            ['n', 0b1000000000000000], // 2 byte checksum
            ['N', 0b11101110111011101110111011101110], // 4 byte source ip
            ['N', 0b11101110111011101110111011101110], // 4 byte destination ip
            ['N', 0b01111111111111111111111111111111], // 4 bytes option 1 (if ihl > 5)
            // udp header
            ['n', 0b1000000000000000], // 2 bytes source port
            ['n', 0b1000000000000000], // 2 bytes destination port
            ['n', 0b1000000000000000], // 2 bytes length
            ['n', 0b1000000000000000], // 2 bytes checksum
            // udp data
            ['H*', 'test'], // 4 bytes n times
        ]);
        $packet = $this->sniffer->parsePacket($rawPacket);
        $this->assertTrue(is_array($packet));
        $this->assertEquals(32768, $packet['udpSourcePort']); // 2 bytes source port
        $this->assertEquals(32768, $packet['udpDestinationPort']); // 2 bytes destination port
        $this->assertEquals(32768, $packet['udpLength']); // 2 bytes length
        $this->assertEquals(32768, $packet['udpChecksum']); // 2 bytes checksum
        $this->assertEquals('test', $packet['data']);
    }

    public function testIcmpPacket()
    {
        $rawPacket = $this->buildRawPacket([
            // ip header
            ['C', 0b100000110], // 1 byte (4 bit version / 4 bit header length)
            ['C', 0b10000000], // 1 byte tos
            ['n', 0b1000000000000000], // 2 bytes length
            ['n', 0b1000000000000000], // 2 bytes id
            ['n', 0b1001000000000000], // 2 bytes (3 bit ip flags / 13 bits fragment offset)
            ['C', 0b10000000], // 1 byte ttl
            ['C', 0b00000001], // 1 byte ip protocol
            ['n', 0b1000000000000000], // 2 byte checksum
            ['N', 0b11101110111011101110111011101110], // 4 byte source ip
            ['N', 0b11101110111011101110111011101110], // 4 byte destination ip
            ['N', 0b01111111111111111111111111111111], // 4 bytes option 1 (if ihl > 5)
            // udp header
            ['C', 0b10000000], // 1 byte type
            ['C', 0b10000000], // 1 byte code
            ['n', 0b1000000000000000], // 2 bytes checksum
            ['N', 0b01111111111111111111111111111111], // 4 bytes other message secific informations
            // udp data
            ['H*', 'test'], // 4 bytes n times
        ]);
        $packet = $this->sniffer->parsePacket($rawPacket);
        $this->assertTrue(is_array($packet));
        $this->assertEquals(128, $packet['icmpType']); // 1 byte type
        $this->assertEquals(128, $packet['icmpCode']); // 1 byte code
        $this->assertEquals(32768, $packet['icmpChecksum']); // 2 bytes checksum
        $this->assertEquals(2147483647, $packet['icmpInformation']); // 4 bytes other message secific informations
        $this->assertEquals('test', $packet['data']);
    }

    private function buildRawPacket($items)
    {
        $parameters = [''];
        foreach ($items as $item) {
            $parameters[0] .= $item[0];
            if ($item[0] == 'H*') {
                $data = $item[1];
                $item[1] = '';
                foreach (str_split($data) as $chr) {
                    $hex = dechex(ord($chr));
                    $item[1] .= strlen($hex) == 2 ? $hex : '0' . $hex;
                }
            }
            $parameters[] = $item[1];
        }
        return call_user_func_array('pack', $parameters);
    }


}
