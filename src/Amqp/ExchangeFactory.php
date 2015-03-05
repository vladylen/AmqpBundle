<?php
namespace M6Web\Bundle\AmqpBundle\Amqp;

use M6Web\Bundle\AmqpBundle\Exception\InvalidArgumentException;
use M6Web\Bundle\AmqpBundle\Factory\ExchangeFactory as ExchangeFactoryInterface;

/**
 * @author Warnar Boekkooi <warnar@boekkooi.net>
 */
class ExchangeFactory implements ExchangeFactoryInterface
{
    protected $connection;

    protected $exchanges = array();

    public function __construct(\AMQPConnection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * {@inheritdoc}
     */
    public function register($name, $type, $passive = false, $durable = true, $auto_delete = false, array $arguments = array())
    {
        $this->exchanges[$name] = array(
            'name' => $name,
            'type' => $type,
            'arguments' => $arguments,

            // flags
            'passive' => $passive,
            'durable' => $durable,
            'auto_delete' => $auto_delete,
        );
    }

    /**
     * {@inheritdoc}
     */
    public function create($name)
    {
        if (!isset($this->exchanges[$name])) {
            throw new InvalidArgumentException(
                sprintf("Exchange configuration with name '%s' doesn't exist", $name)
            );
        }
        $options = $this->exchanges[$name];

        // Connect to server
        if (!$this->connection->isConnected()) {
            $this->connection->connect();
        }

        // Create and declare an exchange
        $exchange = $this->createExchange(
            $this->createChannel($this->connection)
        );
        $exchange->setName($options['name']);
        $exchange->setType($options['type']);
        $exchange->setArguments($options['arguments']);
        $exchange->setFlags(
            ($options['passive'] ? AMQP_PASSIVE : AMQP_NOPARAM) |
            ($options['durable'] ? AMQP_DURABLE : AMQP_NOPARAM) |
            ($options['auto_delete'] ? AMQP_AUTODELETE : AMQP_NOPARAM)
        );

        return $exchange;
    }

    protected function createChannel(\AMQPConnection $connection)
    {
        return new \AMQPChannel($connection);
    }

    protected function createExchange(\AMQPChannel $channel)
    {
        return new \AMQPExchange($channel);
    }
}
