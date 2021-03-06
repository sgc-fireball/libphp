<?php declare(strict_types=1);

namespace HRDNS\System\MessageQueue;

interface MessageQueueInterface
{

    /**
     * @param array $message
     * @return MessageQueueInterface
     */
    public function add(array $message): MessageQueueInterface;

    /**
     * @return array|null
     */
    public function next();

}
