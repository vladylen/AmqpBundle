<?php
namespace Tests\Units\M6Web\Bundle\AmqpBundle;

use M6Web\Bundle\AmqpBundle\Producer;

/**
 * Producer
 */
class ProducerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Producer
     */
    private $producer;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\AMQPExchange
     */
    private $exchange;

    protected function setUp()
    {
        $this->exchange = $this->getMockBuilder('\AMQPExchange')
            ->disableOriginalConstructor()
            ->getMock();

        $this->producer = new Producer($this->exchange);
    }

    protected function tearDown()
    {
        $this->exchange =
        $this->producer = null;
    }

    /**
     * @test
     */
    public function i_should_not_be_forced_to_give_any_options()
    {
        $this->assertEquals(
            array('publish_attributes' => array(), 'routing_keys' => array()),
            $this->producer->getOptions()
        );
    }

    /**
     * @test
     */
    public function i_should_be_able_to_create_with_my_options()
    {
        $options = array(
            'publish_attributes' => array('pub'),
            'routing_keys' => array('route_me'),
            'my_custom_option' => true,
        );
        $producer = new Producer($this->exchange, $options);

        $this->assertEquals($options, $producer->getOptions());
    }
}
