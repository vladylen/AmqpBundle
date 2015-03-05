<?php
namespace M6Web\Bundle\AmqpBundle\Factory;

use M6Web\Bundle\AmqpBundle\Exception\InvalidArgumentException;

/**
 * ConsumerFactory
 */
class ConsumerFactory
{
    /**
     * build the consumer class
     *
     * @param QueueFactory $queueFactory
     * @param string $class Consumer class name
     * @param string $queueName The queue name
     * @return \M6Web\Bundle\AmqpBundle\Consumer
     */
    public function get(QueueFactory $queueFactory, $class, $queueName)
    {
        if (!class_exists($class)) {
            throw new InvalidArgumentException(
                sprintf("Consumer class '%s' doesn't exist", $class)
            );
        }

        // Create the consumer
        return new $class(
            $queueFactory->create($queueName)
        );
    }
}
