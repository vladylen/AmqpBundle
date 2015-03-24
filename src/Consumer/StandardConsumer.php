<?php
namespace M6Web\Bundle\AmqpBundle\Consumer;

use Doctrine\Common\Inflector\Inflector;
use M6Web\Bundle\AmqpBundle\Consumer;

/**
 * Consumer
 */
abstract class StandardConsumer implements Consumer
{
    /**
     * @var \AMQPQueue[]
     */
    private $queues;

    /**
     * @param \AMQPQueue[] $queues
     */
    public function __construct(array $queues)
    {
        $this->queues = $queues;
    }

    /**
     * Consume a single message.
     *
     * @return mixed
     */
    public function consume($limit = 50)
    {
        $self = $this;
        $lastQueue = end($this->queues);

        foreach ($this->queues as $queue) {
            # In case of multiple queues we bind without any arguments
            # This allows multiple to be consumed by one callback
            if($queue !== $lastQueue) {
                $queue->consume();
                continue;
            }

            # The last queue is bound to the callback
            $queue->consume(function(\AMQPEnvelope $message, \AMQPQueue $queue) use ($self, &$limit) {

                $self->dispatchMessage($message, $queue);

                $limit--;
                return $limit > 0;
            });
        }
    }

    protected function dispatchMessage(\AMQPEnvelope $message, \AMQPQueue $queue)
    {
        $method = $this->resolveMethodName($message->getRoutingKey());

        if (method_exists($this, $method) && $this->$method($message, $queue)) {
            $queue->ack($message->getDeliveryTag());
            return;
        }

        $queue->reject($message->getDeliveryTag(), AMQP_REQUEUE);
    }

    /**
     * @param string $routingKey
     * @return string
     */
    protected function resolveMethodName($routingKey)
    {
        return 'on' . Inflector::classify($routingKey);
    }
}
