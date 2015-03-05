<?php

namespace M6Web\Bundle\AmqpBundle\Event;

use Symfony\Component\EventDispatcher\Event as SymfonyEvent;

/**
 * Command Event
 */
class Command extends SymfonyEvent implements DispatcherInterface
{
    /**
     * @var float
     */
    protected $endTime;

    /**
     * @var float
     */
    protected $startTime;

    /**
     * @var string
     */
    protected $command;

    /**
     * @var array
     */
    protected $arguments;

    /**
     * @var mixed
     */
    protected $return;

    /**
     * {@inheritdoc}
     */
    public function setCommand($command)
    {
        $this->command = $command;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setStartTime($start)
    {
        $this->startTime = $start;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setEndTime($end)
    {
        $this->endTime = $end;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setArguments(array $args)
    {
        $this->arguments = $args;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setReturn($value)
    {
        $this->return = $value;

        return $this;
    }

    /**
     * Get the command associated with this event
     *
     * @return string
     */
    public function getCommand()
    {
        return $this->command;
    }

    /**
     * get the arguments
     *
     * @return array
     */
    public function getArguments()
    {
        return $this->arguments;
    }

    /**
     * get the return value
     *
     * @return mixed
     */
    public function getReturn()
    {
        return $this->return;
    }

    /**
     * return the exec time
     *
     * @return float
     */
    public function getExecutionTime()
    {
        return $this->endTime - $this->startTime;
    }

    /**
     * Alias of getExecutionTime for the statsd bundle
     * In ms
     *
     * @return float
     */
    public function getTiming()
    {
        return $this->getExecutionTime() * 1000;
    }
}
