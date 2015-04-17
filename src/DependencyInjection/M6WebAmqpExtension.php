<?php
namespace M6Web\Bundle\AmqpBundle\DependencyInjection;

use M6Web\Bundle\AmqpBundle\Exception\InvalidConfigurationException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class M6WebAmqpExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config        = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('global.yml');

        if ($config['debug']) {
            $loader->load('debug.yml');
        }

        $this->loadConnections($container, $config);
        $this->loadExchanges($container, $config);
        $this->loadQueues($container, $config);
        $this->loadProducers($container, $config);
        $this->loadConsumers($container, $config);
    }

    /**
     * @param ContainerBuilder $container
     * @param array            $config
     */
    protected function loadConnections(ContainerBuilder $container, array $config)
    {
        foreach ($config['connections'] as $key => $connection) {
            $this->defineConnection($container, $key, $connection);
        }
        $container->setParameter(
            'm6_web_amqp.connections',
            array_keys($config['connections'])
        );
    }

    /**
     * @param ContainerBuilder $container
     * @param array            $config
     */
    protected function loadExchanges(ContainerBuilder $container, array $config)
    {
        $exchanges = array();
        foreach ($config['exchanges'] as $name => $options) {
            $this->registerConnectionExchange($container, $name, $options);

            $exchanges[$options['connection']][] = $name;
        }

        foreach ($exchanges as $connection => $exchangeNames) {
            $container->setParameter(
                sprintf('m6_web_amqp.%s.exchanges', $connection),
                $exchangeNames
            );
        }
    }

    /**
     * @param ContainerBuilder $container
     * @param array            $config
     */
    protected function loadQueues(ContainerBuilder $container, array $config)
    {
        $queues = array();
        foreach ($config['queues'] as $name => $options) {
            $this->registerConnectionQueue($container, $name, $options);

            $queues[$this->findConnectionByExchange($container, $options['exchange'])][] = $name;
        }

        foreach ($queues as $connection => $queueNames) {
            $container->setParameter(
                sprintf('m6_web_amqp.%s.queues', $connection),
                $queueNames
            );
        }
    }

    /**
     * @param ContainerBuilder $container
     * @param array            $config
     */
    protected function loadProducers(ContainerBuilder $container, array $config)
    {
        foreach ($config['producers'] as $key => $producer) {
            $this->defineProducer($container, $key, $producer);
        }

        $container->setParameter('m6_web_amqp.producers', array_keys($config['producers']));
    }

    /**
     * @param ContainerBuilder $container
     * @param array            $config
     */
    protected function loadConsumers(ContainerBuilder $container, array $config)
    {
        foreach ($config['consumers'] as $key => $options) {
            $this->defineConsumer($container, $key, $options);
        }

        $container->setParameter('m6_web_amqp.consumers', array_keys($config['consumers']));
    }

    /**
     * @param ContainerBuilder $container
     * @param $connection
     * @param $config
     * @return string
     */
    protected function defineConnection(ContainerBuilder $container, $connection, array $config)
    {
        $serviceId = $this->getConnectionServiceId($connection);

        // Create connection service
        $definition = new DefinitionDecorator('m6_web_amqp.abstract.connection');
        $definition
            ->addMethodCall('setHost ', [$config['host']])
            ->addMethodCall('setPort', [$config['port']])
            ->addMethodCall('setReadTimeout', [$config['timeout']])
            ->addMethodCall('setLogin', [$config['login']])
            ->addMethodCall('setPassword', [$config['password']])
            ->addMethodCall('setVhost', [$config['vhost']]);
        $container->setDefinition($serviceId, $definition);

        // Create related services
        $this->defineConnectionExchangeFactory($container, $serviceId);
        $this->defineConnectionQueueFactory($container, $serviceId);

        return $serviceId;
    }

    /**
     * @param ContainerBuilder $container
     * @param string $serviceId
     */
    protected function defineConnectionExchangeFactory(ContainerBuilder $container, $serviceId)
    {
        $definition = new DefinitionDecorator('m6_web_amqp.abstract.connection.exchange_factory');
        $definition->replaceArgument(0, new Reference($serviceId));

        $container->setDefinition($serviceId.'.exchange', $definition);
    }

    /**
     * @param ContainerBuilder $container
     * @param string $serviceId
     */
    private function defineConnectionQueueFactory(ContainerBuilder $container, $serviceId)
    {
        $definition = new DefinitionDecorator('m6_web_amqp.abstract.connection.queue_factory');
        $definition->replaceArgument(0, new Reference($serviceId));

        $container->setDefinition($serviceId.'.queue', $definition);
    }

    /**
     * @param ContainerBuilder $container
     * @param $exchange
     * @param $options
     */
    protected function registerConnectionExchange(ContainerBuilder $container, $exchange, array $options)
    {
        $connection = $options['connection'];
        $this->requireConnection($container, $connection);

        // Register the exchange
        $container
            ->getDefinition($this->getConnectionServiceId($connection).'.exchange')
            ->addMethodCall(
                'register',
                [
                    $exchange,
                    $options['type'],
                    $options['passive'],
                    $options['durable'],
                    $options['auto_delete'],
                    $options['arguments']
                ]
            );

        // Define a abstract exchange
        $abstractExchange = new Definition(\AMQPExchange::class);
        $abstractExchange
            ->setPublic(false)
            ->setAbstract(true)
            ->setFactory([
                new Reference(
                    $this->getConnectionServiceId($connection).'.exchange'
                ),
                'create'
            ])
            ->setArguments([ null ]);

        $container->setDefinition(
            $this->getAbstractExchangeServiceId($exchange),
            $abstractExchange
        );
    }

    /**
     * @param ContainerBuilder $container
     * @param string $queue
     * @param array $options
     */
    protected function registerConnectionQueue(ContainerBuilder $container, $queue, array $options)
    {
        $exchange = $options['exchange'];
        $this->requireExchange($container, $exchange);

        $connection = $this->findConnectionByExchange($container, $exchange);

        // Register the queue
        $container
            ->getDefinition($this->getConnectionServiceId($connection).'.queue')
            ->addMethodCall(
                'register',
                [
                    $queue,
                    $exchange,
                    $options['passive'],
                    $options['durable'],
                    $options['exclusive'],
                    $options['auto_delete'],
                    $options['arguments'],
                    $options['routing_keys']
                ]
            );

        // Define a abstract queue
        $abstractQueue = new Definition(\AMQPQueue::class);
        $abstractQueue
            ->setPublic(false)
            ->setAbstract(true)
            ->setFactory([
                new Reference(sprintf(
                    '%s.exchange',
                    $this->getConnectionServiceId($connection)
                )),
                'create'
            ])
            ->setArguments([ null ]);

        $container->setDefinition(
            $this->getAbstractQueueServiceId($queue),
            $abstractQueue
        );
    }

    /**
     * @param ContainerBuilder $container
     * @param $key
     * @param $producer
     */
    protected function defineProducer(ContainerBuilder $container, $key, $producer)
    {
        $this->requireExchange($container, $producer['exchange']);

        // Create exchange
        $exchangeId = sprintf('m6_web_amqp.producer_exchange.%s', $key);
        $exchange = new DefinitionDecorator(
            $this->getAbstractExchangeServiceId($producer['exchange'])
        );
        $exchange
            ->setPublic(false)
            ->replaceArgument(0, $producer['exchange']);
        $container->setDefinition($exchangeId, $exchange);

        // Create producer definition
        $container->setDefinition(
            sprintf('m6_web_amqp.producer.%s', $key),
            new Definition(
                $producer['class'],
                [
                    new Reference($exchangeId),
                    [
                        'routing_keys' => $producer['routing_keys'],
                        'publish_attributes' => $producer['publish_attributes'],
                    ]
                ]
            )
        );
    }

    /**
     * @param ContainerBuilder $container
     * @param string $key
     * @param array $options
     */
    protected function defineConsumer(ContainerBuilder $container, $key, array $options)
    {
        $queueServices = [];
        foreach ($options['queue'] as $queueName) {
            $queueServices[] = $this->defineConsumerQueue($container, $key, $queueName);
        }

        $consumerId = sprintf('m6_web_amqp.consumer.%s', $key);

        $service = new Definition();
        $service
            ->addMethodCall('setQueues', [ $queueServices ])
            ->addTag('m6_web_amqp.internal.consumer', ['service' => $options['service']]);

        $container->setDefinition($consumerId, $service);
    }

    /**
     * @param ContainerBuilder $container
     * @param string $consumer
     * @param string $queue
     * @return Reference
     */
    protected function defineConsumerQueue(ContainerBuilder $container, $consumer, $queue)
    {
        $queueId = sprintf('m6_web_amqp.consumer_queue.%s.%s', $consumer, $queue);
        $connection = $this->findConnectionByQueue($container, $queue);

        // Create exchange based on the factory
        $service = new DefinitionDecorator(
            $this->getAbstractQueueServiceId($queue)
        );
        $service
            ->setPublic(false)
            ->setFactory([
                new Reference(
                    $this->getConnectionServiceId($connection) . '.queue'
                ),
                'create'
            ])
            ->replaceArgument(0, $queue);
        $container->setDefinition($queueId, $service);

        return new Reference($queueId);
    }

    private function requireConnection(ContainerBuilder $container, $name)
    {
        if (!$container->has($this->getConnectionServiceId($name))) {
            throw new InvalidConfigurationException(
                sprintf('No connection with name `%s` is defined.', $name)
            );
        }
    }

    private function requireExchange(ContainerBuilder $container, $name)
    {
        if (!$container->has($this->getAbstractExchangeServiceId($name))) {
            throw new InvalidConfigurationException(
                sprintf('No exchange with name `%s` is defined.', $name)
            );
        }
    }

    private function getConnectionServiceId($connection)
    {
        return sprintf('m6_web_amqp.connection.%s', $connection);
    }

    private function getAbstractExchangeServiceId($exchange)
    {
        return sprintf('m6_web_amqp.abstract.exchange.%s', $exchange);
    }

    private function getAbstractQueueServiceId($queue)
    {
        return sprintf('m6_web_amqp.abstract.queue.%s', $queue);
    }

    private function findConnectionByExchange(ContainerBuilder $container, $name)
    {
        $connections = $container->getParameter('m6_web_amqp.connections');
        foreach ($connections as $connection) {
            $exchanges = $container->getParameter(
                sprintf('m6_web_amqp.%s.exchanges', $connection)
            );

            if(in_array($name, $exchanges, true)) {
                return $connection;
            }
        }

        throw new InvalidConfigurationException(
            sprintf('No connection for exchange with name `%s` was found.', $name)
        );
    }

    private function findConnectionByQueue(ContainerBuilder $container, $name)
    {
        $connections = $container->getParameter('m6_web_amqp.connections');
        foreach ($connections as $connection) {
            $queues = $container->getParameter(
                sprintf('m6_web_amqp.%s.queues', $connection)
            );

            if(in_array($name, $queues, true)) {
                return $connection;
            }
        }

        throw new InvalidConfigurationException(
            sprintf('No connection for queue with name `%s` was found.', $name)
        );
    }
}
