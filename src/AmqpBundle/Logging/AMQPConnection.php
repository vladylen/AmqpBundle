<?php
namespace M6Web\Bundle\AmqpBundle\Logging;

/**
 * @author Warnar Boekkooi <warnar@boekkooi.net>
 */
class AMQPConnection extends \AMQPConnection
{
    use EventTrigger;

    /**
     * {@inheritdoc}
     */
    public function connect()
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function disconnect()
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function pconnect()
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function pdisconnect()
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function reconnect()
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function preconnect()
    {
        return $this->call(__FUNCTION__, func_get_args());
    }
}
