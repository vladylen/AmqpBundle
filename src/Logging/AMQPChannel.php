<?php
namespace M6Web\Bundle\AmqpBundle\Logging;

/**
 * @author Warnar Boekkooi <warnar@boekkooi.net>
 */
class AMQPChannel extends \AMQPChannel
{
    use EventTrigger;

    /**
     * {@inheritdoc}
     */
    public function commitTransaction()
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function qos($size, $count)
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function rollbackTransaction()
    {
        return $this->call(__FUNCTION__, func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function startTransaction()
    {
        return $this->call(__FUNCTION__, func_get_args());
    }
}
