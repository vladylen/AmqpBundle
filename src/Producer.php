<?php
namespace M6Web\Bundle\AmqpBundle;

/**
 * Producer
 */
class Producer
{
    /**
     * @var \AMQPExchange
     */
    protected $exchange = null;

    /**
     * @var array
     */
    protected $options = [];

    /**
     * __construct
     *
     * @param \AMQPExchange $exchange        Amqp Exchange
     * @param array         $options Producer options
     */
    public function __construct(\AMQPExchange $exchange, array $options = array())
    {
        $this->exchange = $exchange;
        $this->setOptions($options);
    }

    /**
     * @return \AMQPExchange
     */
    public function getExchange()
    {
        return $this->exchange;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param string $message    The message to publish.
     * @param int    $flags      One or more of AMQP_MANDATORY and AMQP_IMMEDIATE.
     * @param array  $attributes One of content_type, content_encoding,
     *                           message_id, user_id, app_id, delivery_mode, priority,
     *                           timestamp, expiration, type or reply_to.
     *
     * @return boolean          TRUE on success or FALSE on failure.
     *
     * @throws \AMQPExchangeException On failure.
     * @throws \AMQPChannelException If the channel is not open.
     * @throws \AMQPConnectionException If the connection to the broker was lost.
     */
    public function publishMessage($message, $flags = AMQP_NOPARAM, array $attributes = array())
    {
        // Merge attributes
        $attributes = empty($attributes) ? $this->options['publish_attributes'] :
                      array_merge($this->options['publish_attributes'], $attributes);

        // Publish the message for each routing keys
        $success = true;
        foreach ($this->options['routing_keys'] as $routingKey) {
            $success &= $this->exchange->publish($message, $routingKey, $flags, $attributes);
        }

        return (boolean) $success;
    }

    protected function setOptions(array $options)
    {
        if (!array_key_exists('publish_attributes', $options)) {
            $options['publish_attributes'] = [];
        }

        if (!array_key_exists('routing_keys', $options)) {
            $options['routing_keys'] = [];
        }

        $this->options = $options;
    }
}
