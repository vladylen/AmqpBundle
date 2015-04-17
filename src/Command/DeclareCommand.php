<?php
namespace M6Web\Bundle\AmqpBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Warnar Boekkooi <warnar@boekkooi.net>
 */
class DeclareCommand extends BaseCommand
{
    protected function configure()
    {
        $this
            ->setName('m6web:amqp:declare')
            ->setDescription('Declare AMQP queues and exchanges for a connection')
            ->addArgument(
                'connections',
                InputArgument::OPTIONAL | InputArgument::IS_ARRAY,
                'A list of connection names, if none are provided all connections are used.'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $connections = $input->getArgument('connections');
        if (empty($connection)) {
            $connections = $this->getContainer()->getParameter('m6_web_amqp.connections');
        }

        foreach ($connections as $connection) {
            $output->writeln(sprintf('Connection %s', $connection));
            $this->declareConnection($connection, $output);
        }
    }

    protected function declareConnection($connection, OutputInterface $output)
    {
        $container = $this->getContainer();

        /** @var \M6Web\Bundle\AmqpBundle\Factory\ExchangeFactory $factory */
        $output->writeln('Declaring exchanges:');
        $exchangeNames = $container->getParameter(sprintf('m6_web_amqp.%s.exchanges', $connection));
        $factory = $container->get(sprintf('m6_web_amqp.connection.%s.exchange', $connection));
        foreach ($exchangeNames as $name) {
            if (!$factory->create($name)->declareExchange()) {
                $output->writeln(sprintf('<error>X %s</error>', $name));
            } else {
                $output->writeln(sprintf('<info>- %s</info>', $name));
            }
        }

        /** @var \M6Web\Bundle\AmqpBundle\Factory\QueueFactory $factory */
        $output->writeln('Declaring queues:');
        $queueNames = $container->getParameter(sprintf('m6_web_amqp.%s.queues', $connection));
        $factory = $container->get(sprintf('m6_web_amqp.connection.%s.queue', $connection));
        foreach ($queueNames as $name) {
            if ($factory->create($name)->declareQueue() === false) {
                $output->writeln(sprintf('<error>X %s</error>', $name));
            } else {
                $output->writeln(sprintf('<info>- %s</info>', $name));
            }
        }
    }
}
