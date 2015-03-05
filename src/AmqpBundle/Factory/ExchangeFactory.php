<?php
namespace M6Web\Bundle\AmqpBundle\Factory;

/**
 * @author Warnar Boekkooi <warnar@boekkooi.net>
 */
interface ExchangeFactory
{
    /**
     * Register a exchange configuration.
     *
     * @param string $name
     * @param string $type
     * @param bool $passive
     * @param bool $durable
     * @param bool $auto_delete
     * @param array $arguments
     * @return void
     */
    public function register($name, $type, $passive = false, $durable = true, $auto_delete = false, array $arguments = array());

    /**
     * Create a exchange with the given name.
     *
     * @param $name
     * @return \AMQPExchange
     */
    public function create($name);
}
