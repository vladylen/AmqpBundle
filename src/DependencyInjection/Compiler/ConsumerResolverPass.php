<?php
namespace M6Web\Bundle\AmqpBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author Warnar Boekkooi <warnar@boekkooi.net>
 */
class ConsumerResolverPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $serviceIds = $container->findTaggedServiceIds('m6_web_amqp.internal.consumer');

        foreach ($serviceIds as $serviceId => $tags) {
            $serviceReference = $tags[0]['service'];
            $service = $container->getDefinition($serviceReference);

            $baseDefinition = $container->getDefinition($serviceId);
            foreach ($baseDefinition->getMethodCalls() as $methodInfo) {
                $service->addMethodCall($methodInfo[0], $methodInfo[1]);
            }

            $container->removeDefinition($serviceId);
            $container->setAlias($serviceId, $serviceReference);
        }
    }
}
