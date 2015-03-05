<?php
namespace M6Web\Bundle\AmqpBundle\Logging;

use M6Web\Bundle\AmqpBundle\Amqp\ExchangeFactory as BaseFactory;
use M6Web\Bundle\AmqpBundle\Exception\InvalidArgumentException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @author Warnar Boekkooi <warnar@boekkooi.net>
 */
class ExchangeFactory extends BaseFactory
{
    protected $eventDispatcher = null;

    protected $eventClass = null;

    /*
     * @param EventDispatcherInterface $eventDispatcher The eventDispatcher object, which implement the notify method
     * @param string $eventClass      The event class used to create an event and send it to the event dispatcher
     */
    public function __construct(\AMQPConnection $connection, EventDispatcherInterface $eventDispatcher, $eventClass)
    {
        parent::__construct($connection);

        if (!is_subclass_of($eventClass, 'M6Web\Bundle\AmqpBundle\Event\DispatcherInterface')) {
            throw new InvalidArgumentException("The Event class : ".$eventClass." must implement DispatcherInterface");
        }

        $this->eventDispatcher = $eventDispatcher;
        $this->eventClass = $eventClass;
    }

    protected function createChannel(\AMQPConnection $connection)
    {
        $channel = new AMQPChannel($connection);
        $channel->setEventDispatcher($this->eventDispatcher, $this->eventClass);
        return $channel;
    }

    protected function createExchange(\AMQPChannel $channel)
    {
        $exchange = new AMQPExchange($channel);
        $exchange->setEventDispatcher($this->eventDispatcher, $this->eventClass);
        return $exchange;
    }
}