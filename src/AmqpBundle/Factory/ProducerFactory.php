<?php
namespace M6Web\Bundle\AmqpBundle\Factory;

use M6Web\Bundle\AmqpBundle\Exception\InvalidArgumentException;

/**
 * ProducerFactory
 */
class ProducerFactory
{
    /**
     * build the producer class
     *
     * @param ExchangeFactory $exchangeFactory
     * @param string $class Provider class name
     * @param string $exchangeName
     * @param array $options
     * @return \M6Web\Bundle\AmqpBundle\Producer
     */
    public function get(ExchangeFactory $exchangeFactory, $class, $exchangeName, array $options = array())
    {
        if (!class_exists($class)) {
            throw new InvalidArgumentException(
                sprintf("Producer class '%s' doesn't exist", $class)
            );
        }

        return new $class (
            $exchangeFactory->create($exchangeName),
            $options
        );
    }
}
