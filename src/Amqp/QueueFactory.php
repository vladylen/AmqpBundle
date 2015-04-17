<?php
namespace M6Web\Bundle\AmqpBundle\Amqp;

use M6Web\Bundle\AmqpBundle\Exception\InvalidArgumentException;
use M6Web\Bundle\AmqpBundle\Factory\QueueFactory as QueueFactoryInterface;

/**
 * @author Warnar Boekkooi <warnar@boekkooi.net>
 */
class QueueFactory implements QueueFactoryInterface
{
    protected $connection;

    protected $queues = array();

    public function __construct(\AMQPConnection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * {@inheritdoc}
     */
    public function register($name, $exchange, $passive = false, $durable = true, $exclusive = false, $auto_delete = false, array $arguments = array(), array $routing_keys = array(null))
    {
        $this->queues[$name] = array(
            'exchange' => $exchange,

            'name' => $name,
            'arguments' => $arguments,
            'routing_keys' => $routing_keys,

            // flags
            'passive' => $passive,
            'durable' => $durable,
            'exclusive' => $exclusive,
            'auto_delete' => $auto_delete,
        );
    }

    /**
     * {@inheritdoc}
     */
    public function create($name)
    {
        if (!isset($this->queues[$name])) {
            throw new InvalidArgumentException(
                sprintf("Queue configuration with name '%s' doesn't exist", $name)
            );
        }
        $options = $this->queues[$name];

        if (!$this->connection->isConnected()) {
            $this->connection->connect();
        }

        // Create the queue
        $queue = $this->createQueue(
            $this->createChannel($this->connection)
        );
        $queue->setName($options['name']);
        $queue->setArguments($options['arguments']);
        $queue->setFlags(
            ($options['passive'] ? AMQP_PASSIVE : AMQP_NOPARAM) |
            ($options['durable'] ? AMQP_DURABLE : AMQP_NOPARAM) |
            ($options['exclusive'] ? AMQP_EXCLUSIVE : AMQP_NOPARAM) |
            ($options['auto_delete'] ? AMQP_AUTODELETE : AMQP_NOPARAM)
        );

        // Declare the queue
        $queue->declareQueue();

        // Bind the queue to some routing keys
        foreach ($options['routing_keys'] as $routingKey) {
            $queue->bind($options['exchange'], $routingKey);
        }

        return $queue;
    }

    protected function createChannel(\AMQPConnection $connection)
    {
        return new \AMQPChannel($connection);
    }

    protected function createQueue(\AMQPChannel $channel)
    {
        return new \AMQPQueue($channel);
    }
}
