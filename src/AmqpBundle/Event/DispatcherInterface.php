<?php

namespace M6Web\Bundle\AmqpBundle\Event;


/**
 * Dispatcher interface
 */
interface DispatcherInterface
{
    /**
     * Set the amqp command associated with this event
     *
     * @param string $command
     * @return $this
     */
    public function setCommand($command);

    /**
     * Set start time
     *
     * @param float $start A unix timestamp with microseconds
     * @return $this
     */
    public function setStartTime($start);

    /**
     * Set end time
     *
     * @param float $end A unix timestamp with microseconds
     * @return $this
     */
    public function setEndTime($end);

    /**
     * Set the arguments
     *
     * @param array $args
     * @return $this
     */
    public function setArguments(array $args);

    /**
     * Set the return value
     *
     * @param mixed $value
     * @return $this
     */
    public function setReturn($value);
}
