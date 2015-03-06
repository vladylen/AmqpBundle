<?php

namespace M6Web\Bundle\AmqpBundle\DependencyInjection;

use M6Web\Bundle\AmqpBundle\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

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
        $loader->load('services.yml');

        if ($config['debug']) {
            $loader->load('data_collector.yml');
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
        $debug = $config['debug'];
        foreach ($config['connections'] as $key => $connection) {
            $serviceId = sprintf('m6_web_amqp.connection.%s', $key);

            // Create connection service
            $exchangeFactoryClass = $debug ? 'M6Web\Bundle\AmqpBundle\Logging\AMQPConnection' : 'AMQPConnection';
            $definition = new Definition($exchangeFactoryClass);
            $definition
                ->addMethodCall('setHost ', [$connection['host']])
                ->addMethodCall('setPort', [$connection['port']])
                ->addMethodCall('setReadTimeout', [$connection['timeout']])
                ->addMethodCall('setLogin', [$connection['login']])
                ->addMethodCall('setPassword', [$connection['password']])
                ->addMethodCall('setVhost', [$connection['vhost']]);
            if ($debug) {
                $definition->addMethodCall('setEventDispatcher', array(
                    new Reference('event_dispatcher'),
                    $container->getParameter('m6_web_amqp.event.command.class')
                ));
            }
            if (!$connection['lazy']) {
                $definition->addMethodCall('connect');
            } elseif (!method_exists($definition, 'setLazy')) {
                throw new \InvalidArgumentException('It\'s not possible to declare a service as lazy. Are you using Symfony 2.3?');
            }
            $container->setDefinition($serviceId, $definition);

            // Create connection exchange factory
            $exchangeFactoryClass = 'M6Web\BundleAmqpBundle\Amqp\ExchangeFactory';
            $exchangeFactoryArgs = array( new Reference($serviceId) );
            if ($debug) {
                $exchangeFactoryClass = 'M6Web\Bundle\AmqpBundle\Logging\ExchangeFactory';
                $exchangeFactoryArgs[] = new Reference('event_dispatcher');
                $exchangeFactoryArgs[] = $container->getParameter('m6_web_amqp.event.command.class');
            }
            $container->setDefinition(
                sprintf('m6_web_amqp.connection.%s.exchange', $key),
                new Definition($exchangeFactoryClass, $exchangeFactoryArgs)
            );

            // Create connection queue factory
            $queue_factory_class = 'M6Web\BundleAmqpBundle\Amqp\QueueFactory';
            $queue_factory_args = array( new Reference($serviceId) );
            if ($debug) {
                $queue_factory_class = 'M6Web\Bundle\AmqpBundle\Logging\QueueFactory';
                $queue_factory_args[] = new Reference('event_dispatcher');
                $queue_factory_args[] = $container->getParameter('m6_web_amqp.event.command.class');
            }
            $container->setDefinition(
                sprintf('m6_web_amqp.connection.%s.queue', $key),
                new Definition($queue_factory_class, $queue_factory_args)
            );
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
            $factoryDefinition = $container->getDefinition(
                sprintf('m6_web_amqp.connection.%s.exchange', $options['connection'])
            );

            $factoryDefinition->addMethodCall(
                'register',
                [
                    $name,
                    $options['type'],
                    $options['passive'],
                    $options['durable'],
                    $options['auto_delete'],
                    $options['arguments']
                ]
            );

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
            if (!isset($config['exchanges'][$options['exchange']])) {
                throw new InvalidConfigurationException(sprintf('Exchange for queue `%s` with name `%s` is not defined.', $name, $options['exchange']));
            }
            $connection = $config['exchanges'][$options['exchange']]['connection'];

            $factoryDefinition = $container->getDefinition(
                sprintf('m6_web_amqp.connection.%s.queue', $connection)
            );

            $factoryDefinition->addMethodCall(
                'register',
                [
                    $name,
                    $options['exchange'],
                    $options['passive'],
                    $options['durable'],
                    $options['exclusive'],
                    $options['auto_delete'],
                    $options['arguments'],
                    $options['routing_keys']
                ]
            );

            $queues[$connection][] = $name;
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
            if (!isset($config['exchanges'][$producer['exchange']])) {
                throw new InvalidConfigurationException(sprintf('Exchange with name `%s` is not defined.', $producer['exchange']));
            }
            $connection = $config['exchanges'][$producer['exchange']]['connection'];
            $lazy = $config['connections'][$connection]['lazy'];

            // Create producer definition
            $definition = new Definition($producer['class']);
            $definition
                ->setFactoryService('m6_web_amqp.producer_factory')
                ->setFactoryMethod('get')
                ->setArguments(array(
                    new Reference(sprintf('m6_web_amqp.connection.%s.exchange', $connection)),
                    $producer['class'],
                    $producer['exchange'],
                    [
                        'routing_keys' => $producer['routing_keys'],
                        'publish_attributes' => $producer['publish_attributes'],
                    ]
                ));

            if ($lazy) {
                $definition->setLazy(true);
            }

            $container->setDefinition(
                sprintf('m6_web_amqp.producer.%s', $key),
                $definition
            );
        }
    }

    /**
     * @param ContainerBuilder $container
     * @param array            $config
     */
    protected function loadConsumers(ContainerBuilder $container, array $config)
    {
        foreach ($config['consumers'] as $key => $consumer) {
            if (!isset($config['queues'][$consumer['queue']])) {
                throw new InvalidConfigurationException(sprintf('Query with name `%s` is not defined.', $consumer['queue']));
            }
            $exchange = $config['queues'][$consumer['queue']]['exchange'];

            if (!isset($config['exchanges'][$exchange])) {
                throw new InvalidConfigurationException(sprintf('Exchange with name `%s` is not defined.', $exchange));
            }
            $connection = $config['exchanges'][$exchange]['connection'];

            $lazy = $config['connections'][$connection]['lazy'];

            // Create the consumer with the factory
            $definition = new Definition($consumer['class']);
            $definition
                ->setFactoryService('m6_web_amqp.consumer_factory')
                ->setFactoryMethod('get')
                ->setArguments(array(
                    new Reference(sprintf('m6_web_amqp.connection.%s.queue', $connection)),
                    $consumer['class'],
                    $consumer['queue'],
                ));

            if ($lazy) {
                $definition->setLazy(true);
            }

            $container->setDefinition(
                sprintf('m6_web_amqp.consumer.%s', $key),
                $definition
            );
        }
    }

}
