<?php
namespace M6Web\Bundle\AmqpBundle\Factory;

/**
 * @author Warnar Boekkooi <warnar@boekkooi.net>
 */
interface QueueFactory
{
    /**
     * Register a exchange configuration.
     *
     * @param string $name
     * @param string $exchange
     * @param bool $passive
     * @param bool $durable
     * @param bool $exclusive
     * @param bool $auto_delete
     * @param array $arguments
     * @param array $routing_keys
     * @return
     */
    public function register($name, $exchange, $passive = false, $durable = true, $exclusive = false, $auto_delete = false, array $arguments = array(), array $routing_keys = array(null));

    /**
     * Create a exchange with the given name.
     *
     * @param $name
     * @return \AMQPQueue
     */
    public function create($name);
}
