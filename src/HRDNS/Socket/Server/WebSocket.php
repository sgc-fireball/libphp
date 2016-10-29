<?php

namespace HRDNS\Socket\Server;

use HRDNS\Socket\Server\WebSocket\Request;
use HRDNS\Socket\Server\WebSocket\Response;
use HRDNS\Socket\Server\WebSocket\Frame;

/**
 * @abstract
 * @see http://www.websocket.org/echo.html
 */
abstract class WebSocket extends TCPServer
{

    /** @var string */
    protected $server = 'HRDNS-WebSocket/1.0.0';

    /**
     * time in sec. to send the next ping to client
     *
     * @var int
     */
    protected $timeToPing = 30;

    /**
     * time in sec. the client must answer the ping request.
     *
     * @var int
     */
    protected $timeout = 3;

    /**
     * @param ServerClient $client
     * @return void
     */
    public function onConnect(ServerClient $client)
    {
        $client->setAttribute('_websocket_tick', time());
        $client->setAttribute('_websocket_buffer', '');
        $client->setAttribute('_websocket_handshake', 0);
        $client->setAttribute('_websocket_ping_time', 0);
        $client->setAttribute('_websocket_ping_code', '');
        $client->setAttribute('_websocket_ping_latenz', -1);
        $this->onWebSocketConnect($client);
    }

    /**
     * @param ServerClient $client
     * @param string $buffer
     * @return self
     */
    public function onIncoming(ServerClient $client, string $buffer): self
    {
        if (!$client->getAttribute('_websocket_handshake')) {
            return $this->handleHttpRequest($client, $buffer);
        }
        return $this->handleWebSocketRequest($client, $buffer);
    }

    /**
     * @param ServerClient $client
     * @param string $buffer
     * @return void
     */
    public function onOutgoing(ServerClient $client, string $buffer)
    {
        return;
    }

    /**
     * @param ServerClient $client
     * @return void
     */
    public function onDisconnect(ServerClient $client)
    {
        $this->onWebSocketDisconnect($client);
    }

    /**
     * @param ServerClient $client
     * @return void
     */
    public function onTick(ServerClient $client)
    {
        if (!$client->getAttribute('_websocket_handshake')) {
            return;
        }

        $time = (int)$client->getAttribute('_websocket_tick');
        if ($time == time()) {
            return;
        }
        $client->setAttribute('_websocket_tick', time());

        /**
         * check: need to send a new ping?
         */
        $pingTime = (int)$client->getAttribute('_websocket_ping_time');
        if ($pingTime + $this->timeToPing < time()) {
            $this->sendWebSocketPing($client);
        }

        /**
         * check: received a pong?
         */
        $pingTime = (int)$client->getAttribute('_websocket_ping_time');
        if ($client->getAttribute('_websocket_ping_code') && $pingTime + $this->timeout < time()) {
            $this->onDisconnect($client);
            $this->disconnect($client);
            return;
        }

        $this->onWebSocketTick($client);
    }

    /**
     * @param ServerClient $client
     * @param Request $request
     * @return Response
     */
    protected function getResponse(ServerClient $client, Request $request): Response
    {
        $response = Response::createFromRequest($request);
        $response->addHeader('Date', date('r'));
        $response->addHeader('Server', $this->server);
        return $response;
    }

    /**
     * @param ServerClient $client
     * @param Request $request
     * @return self
     */
    protected function acceptConnection(ServerClient $client, Request $request): self
    {
        $acceptKey = $request->getHeader('sec-websocket-key');
        $acceptKey .= '258EAFA5-E914-47DA-95CA-C5AB0DC85B11';
        $acceptKey = base64_encode(sha1($acceptKey, true));

        $response = $this->getResponse($client, $request);
        $response->setCode(101);
        $response->addHeader('Access-Control-Allow-Credentials', 'true');
        $response->addHeader('Access-Control-Allow-Origin', $request->getHeader('origin'));
        $response->addHeader('Connection', 'Upgrade');
        $response->addHeader('Upgrade', 'websocket');
        $response->addHeader('Sec-WebSocket-Accept', $acceptKey);
        $this->send($client, $response);
        return $this;
    }

    /**
     * @param ServerClient $client
     * @param Request $request
     * @return self
     */
    protected function sendBadRequest(ServerClient $client, Request $request): self
    {
        $response = $this->getResponse($client, $request);
        $response->setCode(400);
        $response->addHeader('Connection', 'Close');
        $this->send($client, $response);
        $this->disconnect($client);
        return $this;
    }

    /**
     * @param ServerClient $client
     * @param string $buffer
     * @return self
     */
    public function handleHttpRequest(ServerClient $client, string $buffer): self
    {
        $request = Request::parse($buffer);

        /**
         * @TODO implement policy-file-request
         * request XML policy-file-request
         * response XML allow-access-from
         */

        if (strpos($request->getPath(), '/ws') === false) {
            $this->sendBadRequest($client, $request);
        }
        if (strtolower($request->getHeader('connection')) != 'upgrade') {
            $this->sendBadRequest($client, $request);
        }
        if (strtolower($request->getHeader('upgrade')) != 'websocket') {
            $this->sendBadRequest($client, $request);
        }
        if ($request->getHeader('sec-websocket-version') != 13) {
            $this->sendBadRequest($client, $request);
        }
        $client->setAttribute('_websocket_handshake', 1);
        return $this->acceptConnection($client, $request);
    }

    /**
     * @param ServerClient $client
     * @param string $buffer
     * @return self
     */
    protected function handleWebSocketRequest(ServerClient $client, string $buffer): self
    {
        $buffer = $client->getAttribute('_websocket_buffer') . $buffer;
        $client->setAttribute('_websocket_buffer', '');
        $frame = Frame::decode($buffer);
        if ($frame->isIncomplete()) {
            $client->setAttribute('_websocket_buffer', $buffer);
            return $this;
        }

        if (in_array($frame->getOpcode(), [Frame::TYPE_TEXT, Frame::TYPE_BINARY])) {
            $this->onWebSocketIncoming($client, $frame->getBody());
            return $this;
        }

        if ($frame->getOpCode() == Frame::TYPE_PING) {
            $pong = new Frame(
                [
                    'opcode' => Frame::TYPE_PONG,
                    'body' => $frame->getBody(),
                    'length' => strlen($frame->getBody())
                ]
            );
            $this->send($client, $pong->encode());
            return $this;
        }

        if ($frame->getOpCode() == Frame::TYPE_PONG) {
            if ($client->getAttribute('_websocket_ping_code') == $frame->getBody()) {
                list(, $time) = explode(':', $client->getAttribute('_websocket_ping_code'), 2);
                $client->setAttribute('_websocket_ping_latenz', microtime(true) - $time);
                $client->setAttribute('_websocket_ping_code', '');
                return $this;
            }
        }

        $this->onDisconnect($client);
        $this->disconnect($client);
        return $this;
    }

    /**
     * @param ServerClient $client
     * @param string $buffer
     * @return self
     */
    public function sendWebSocket(ServerClient $client, string $buffer): self
    {
        $this->onWebSocketOutgoing($client, $buffer);
        $frame = new Frame(
            [
                'opcode' => FRAME::TYPE_TEXT,
                'body' => $buffer,
                'length' => strlen($buffer),
                'mask' => (bool)$client->getAttribute('_websocket_mask')
            ]
        );
        $this->send($client, $frame->encode());
        return $this;
    }

    /**
     * @param ServerClient $client
     * @return self
     */
    public function sendWebSocketPing(ServerClient $client): self
    {
        $hash = hash(
            'sha256',
            sprintf(
                '%s_%s_%s',
                $client->getId(),
                uniqid(
                    '',
                    true
                ),
                microtime(
                    true
                )
            )
        );
        $code = sprintf('%s:%s', $hash, microtime(true));
        $client->setAttribute('_websocket_ping_code', $code);
        $client->setAttribute('_websocket_ping_time', time());
        $frame = new Frame(
            [
                'opcode' => FRAME::TYPE_PING,
                'body' => $code,
                'length' => strlen($code),
                'mask' => (bool)$client->getAttribute('_websocket_mask')
            ]
        );
        $this->send($client, $frame->encode());
        return $this;
    }

    /**
     * @param ServerClient $client
     * @return void
     */
    abstract public function onWebSocketConnect(ServerClient $client);

    /**
     * @param ServerClient $client
     * @param string $buffer
     * @return void
     */
    abstract public function onWebSocketIncoming(ServerClient $client, string $buffer);

    /**
     * @param ServerClient $client
     * @return void
     */
    abstract public function onWebSocketTick(ServerClient $client);

    /**
     * @param ServerClient $client
     * @param string $buffer
     * @return void
     */
    abstract public function onWebSocketOutgoing(ServerClient $client, string $buffer);

    /**
     * @param ServerClient $client
     * @return void
     */
    abstract public function onWebSocketDisconnect(ServerClient $client);

}
