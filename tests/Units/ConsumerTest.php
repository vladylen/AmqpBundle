<?php
namespace Tests\Units\M6Web\Bundle\AmqpBundle;

use M6Web\Bundle\AmqpBundle\Consumer;

/**
 * Consumer
 */
class ConsumerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Consumer
     */
    private $consumer;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\AMQPQueue
     */
    private $queue;

    protected function setUp()
    {
        $this->queue = $this->getMockBuilder('\AMQPQueue')
            ->disableOriginalConstructor()
            ->getMock();

        $this->consumer = new Consumer($this->queue);
    }

    protected function tearDown()
    {
        $this->queue =
        $this->consumer = null;
    }

    /**
     * @test
     */
    public function i_should_be_able_to_get_the_raw_queue()
    {
        $this->assertEquals($this->queue, $this->consumer->getQueue());
    }

    /**
     * @test
     */
    public function i_should_be_able_to_get_a_message()
    {
        $this->queue->expects($this->atLeast(2))
            ->method('get')
            ->will($this->returnValueMap(array(
                array(AMQP_AUTOACK, 'auto'),
                array(AMQP_NOPARAM, 'noparam'))
            ));

        $this->assertEquals('auto', $this->consumer->getMessage());
        $this->assertEquals('noparam', $this->consumer->getMessage(AMQP_NOPARAM));
    }

    /**
     * @test
     */
    public function i_should_be_able_to_get_the_current_message_count()
    {
        $this->queue->expects($this->atLeastOnce())
            ->method('getFlags')
            ->willReturn(AMQP_NOPARAM);

        $this->queue->expects($this->atLeast(2))
            ->method('setFlags')
            ->will($this->returnValueMap(array(
                    array(AMQP_NOPARAM | AMQP_PASSIVE, null),
                    array(AMQP_NOPARAM, null))
            ));

        $this->queue->expects($this->atLeastOnce())
            ->method('declareQueue')
            ->willReturn(5);

        $this->assertEquals(5, $this->consumer->getCurrentMessageCount());
    }

    /**
     * @test
     */
    public function i_should_be_able_to_acknowledge_a_message()
    {
        $this->queue->expects($this->atLeast(2))
            ->method('ack')
            ->will($this->returnValueMap(array(
                    array('myTag', AMQP_MULTIPLE, 'multiple'),
                    array('otherTag', AMQP_NOPARAM, 'noparam'))
            ));

        $this->assertEquals('multiple', $this->consumer->ackMessage('myTag', AMQP_MULTIPLE));
        $this->assertEquals('noparam', $this->consumer->ackMessage('otherTag', AMQP_NOPARAM));
    }

    /**
     * @test
     */
    public function i_should_be_able_to_not_acknowledge_a_message()
    {
        $this->queue->expects($this->atLeast(2))
            ->method('nack')
            ->will($this->returnValueMap(array(
                array('myTag', AMQP_REQUEUE, 'requeue'),
                array('otherTag', AMQP_NOPARAM, 'noparam'))
            ));

        $this->assertEquals('requeue', $this->consumer->nackMessage('myTag', AMQP_REQUEUE));
        $this->assertEquals('noparam', $this->consumer->nackMessage('otherTag', AMQP_NOPARAM));
    }

    /**
     * @test
     */
    public function i_should_be_able_to_purge_the_queue()
    {
        $this->queue->expects($this->atLeastOnce())
            ->method('purge');

        $this->consumer->purge();
    }
}
