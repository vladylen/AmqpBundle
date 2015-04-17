<?php
namespace M6Web\Bundle\AmqpBundle\DataCollector;

use Symfony\Component\HttpKernel\DataCollector\DataCollector;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Warnar Boekkooi <warnar@boekkooi.net>
 */
class CommunicationDataCollector extends DataCollector
{
    public function __construct()
    {
        $this->data['commands'] = array();
    }

    /**
     * {@inheritdoc}
     */
    public function collect(Request $request, Response $response, \Exception $exception = null)
    {
    }

    /**
     * Listen for amqp command event
     *
     * @param object $event The event object
     */
    public function onCommand($event)
    {
        $this->data['commands'][] = array(
            'command'   => $event->getCommand(),
            'arguments' => $event->getArguments(),
            'executiontime' => $event->getExecutionTime()
        );
    }

    /**
     * Return command list and number of times they were called
     *
     * @return array The command list and number of times called
     */
    public function getCommands()
    {
        return $this->data['commands'];
    }

    /**
     * Get total time executing commands.
     *
     * @return float
     */
    public function getTotalExecutionTime()
    {
        $ret = 0;
        foreach ($this->data['commands'] as $command) {
            $ret += $command['executiontime'];
        }

        return $ret;
    }

    /**
     * Get average time it took to execute a single command
     *
     * @return float
     */
    public function getAvgExecutionTime()
    {
        return ($this->getTotalExecutionTime()) ? ($this->getTotalExecutionTime() / count($this->data['commands']) ) : 0;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'm6web_amqp_communication';
    }
}
