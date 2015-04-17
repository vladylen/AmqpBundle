<?php
namespace M6Web\Bundle\AmqpBundle\Logging;

/**
 * @author Warnar Boekkooi <warnar@boekkooi.net>
 */
class AMQPExchange extends \AMQPExchange
{
    use EventTrigger;

    /**
     * {@inheritdoc}
     */
    public function bind($exchange_name, $routing_key, $flags = NULL)
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function unbind($exchange_name, $routing_key, $flags = NULL)
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function declareExchange()
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function delete($exchangeName = null, $flags = AMQP_NOPARAM)
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function publish($message, $routing_key = NULL, $flags = NULL, array $headers = NULL)
    {
        return $this->call(__FUNCTION__, func_get_args());
    }
}
