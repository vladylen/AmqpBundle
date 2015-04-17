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
     * @param string[] $routing_keys Override the default routing keys.
     *
     * @return boolean          TRUE on success or FALSE on failure.
     *
     * @throws \AMQPExchangeException On failure.
     * @throws \AMQPChannelException If the channel is not open.
     * @throws \AMQPConnectionException If the connection to the broker was lost.
     */
    public function publish($message, $flags = AMQP_NOPARAM, array $attributes = array(), array $routing_keys = null)
    {
        // Merge attributes
        $attributes = $this->getPublishOptions($attributes);
        $routing_keys = $this->getRoutingKeys($routing_keys);
        if (empty($routing_keys)) {
            return false;
        }

        // Publish the message for each routing keys
        $success = true;
        foreach ($routing_keys as $routingKey) {
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

    protected function getPublishOptions(array $attributes)
    {
        return empty($attributes) ? $this->options['publish_attributes'] :
            array_merge($this->options['publish_attributes'], $attributes);
    }

    protected function getRoutingKeys(array $routing_keys = null)
    {
        if ($routing_keys === null) {
            return $this->options['routing_keys'];
        }
        return $routing_keys;
    }
}
