<?php
namespace M6Web\Bundle\AmqpBundle\Logging;

use M6Web\Bundle\AmqpBundle\Exception\InvalidArgumentException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @author Warnar Boekkooi <warnar@boekkooi.net>
 */
trait EventTrigger
{
    /**
     * Event dispatcher
     *
     * @var EventDispatcherInterface|null
     */
    protected $eventDispatcher = null;

    /**
     * Class of the event notifier
     *
     * @var string
     */
    protected $eventClass = null;

    /**
     * Call a method and notify an event
     *
     * @param string $method    Method name
     * @param array  $arguments Method arguments
     * @return mixed
     */
    protected function call($method, array $arguments)
    {
        $start = microtime(true);

        $rtn = call_user_func_array(array($this, 'parent::'.$method), $arguments);

        $this->notifyEvent($method, $arguments, $rtn, $start, microtime(true));

        return $rtn;
    }

    /**
     * Set an event dispatcher to notify amqp command
     *
     * @param EventDispatcherInterface $eventDispatcher The eventDispatcher object, which implement the notify method
     * @param string $eventClass      The event class used to create an event and send it to the event dispatcher
     *
     * @return void
     */
    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher, $eventClass)
    {
        if (!is_subclass_of($eventClass, 'M6Web\Bundle\AmqpBundle\Event\DispatcherInterface')) {
            throw new InvalidArgumentException("The Event class : ".$eventClass." must implement DispatcherInterface");
        }

        $this->eventDispatcher = $eventDispatcher;
        $this->eventClass = $eventClass;
    }

    /**
     * Notify an event to the event dispatcher
     *
     * @param string $command The command name
     * @param array $arguments Args of the command
     * @param mixed $return Return value of the command
     * @param int $start
     * @param int $end
     */
    private function notifyEvent($command, $arguments, $return, $start, $end)
    {
        if ($this->eventDispatcher === null) {
            return;
        }

        /** @var \M6Web\Bundle\AmqpBundle\Event\DispatcherInterface $event */
        $event = new $this->eventClass();
        $event->setCommand(__CLASS__ . '::' . $command)
            ->setArguments($arguments)
            ->setReturn($return)
            ->setStartTime($start)
            ->setEndTime($end);

        $this->eventDispatcher->dispatch('amqp.command', $event);
    }
}
