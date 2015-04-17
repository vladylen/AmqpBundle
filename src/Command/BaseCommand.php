<?php
namespace M6Web\Bundle\AmqpBundle\Command;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;

/**
 * @author Warnar Boekkooi <warnar@boekkooi.net>
 */
abstract class BaseCommand extends Command implements ContainerAwareInterface
{
    /**
     * @var ContainerInterface|null
     */
    private $container;

    /**
     * @return ContainerInterface
     * @throws \LogicException
     */
    protected function getContainer()
    {
        if (null === $this->container) {
            $application = $this->getApplication();
            if (null === $application) {
                throw new \LogicException('The container cannot be retrieved as the application instance is not yet set.');
            }
            if (!method_exists($application, 'getKernel')) {
                throw new \LogicException('The container cannot be retrieved as the application contains no getKernel method.');
            }

            $this->container = $application->getKernel()->getContainer();
        }

        return $this->container;
    }

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }
}
