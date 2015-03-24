<?php
namespace M6Web\Bundle\AmqpBundle;

/**
 * @author Warnar Boekkooi <warnar@boekkooi.net>
 */
interface Consumer {
    /**
     * @param \AMQPQueue[] $queues
     */
    public function __construct(array $queues);

    /**
     * Consume messages.
     *
     * @param int $amount
     * @return mixed
     */
    public function consume($amount);
}
