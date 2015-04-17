<?php
namespace M6Web\Bundle\AmqpBundle;

use M6Web\Bundle\AmqpBundle\DependencyInjection\Compiler\ConsumerResolverPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * M6WebAmqpBundle
 */
class M6WebAmqpBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new ConsumerResolverPass());
    }

}