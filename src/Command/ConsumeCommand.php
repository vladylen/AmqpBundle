<?php
namespace M6Web\Bundle\AmqpBundle\Command;

use M6Web\Bundle\AmqpBundle\Consumer;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Warnar Boekkooi <warnar@boekkooi.net>
 */
class ConsumeCommand extends BaseCommand
{
    protected function configure()
    {
        $this
            ->setName('m6web:amqp:consume')
            ->setDescription('Declare AMQP queues and exchanges for a connection')
            ->addArgument('name', InputArgument::REQUIRED, 'Consumer Name')
            ->addOption('amount', 'a', InputOption::VALUE_OPTIONAL, 'The amount of messages to consume', 100)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();
        $amount = $input->getOption('amount');
        $consumerName = $input->getArgument('name');

        \Assert\that($consumerName, 'Invalid consumer argument')
            ->string($consumerName)
            ->notBlank();
        \Assert\that(
                $container->has('m6_web_amqp.consumer.' . $consumerName),
                sprintf('Unknown consumer %s', $consumerName)
            )->true();
        \Assert\that($amount)
            ->integerish($amount, 'Amount must be a number')
            ->min(1, 'Amount must be 1 or greater');

        /** @var Consumer $consumer */
        $consumer = $container->get('m6_web_amqp.consumer.' . $consumerName);
        $consumer->consume($amount);
    }
}
