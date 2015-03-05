<?php
namespace M6Web\Bundle\AmqpBundle\Logging;

/**
 * @author Warnar Boekkooi <warnar@boekkooi.net>
 */
class AMQPQueue extends \AMQPQueue
{
    use EventTrigger;

    /**
     * {@inheritdoc}
     */
    public function ack($delivery_tag, $flags = AMQP_NOPARAM)
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function bind($exchange_name, $routing_key = NULL, $arguments = NULL)
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function cancel($consumer_tag = '')
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function consume($callback, $flags = AMQP_NOPARAM, $consumerTag = null)
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function declareQueue()
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function delete($flags = AMQP_NOPARAM)
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function nack($delivery_tag, $flags = AMQP_NOPARAM)
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function reject($delivery_tag, $flags = AMQP_NOPARAM)
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function purge()
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function unbind($exchange_name, $routing_key = NULL, $arguments = NULL)
    {
        return $this->call(__FUNCTION__, func_get_args());
    }
}
