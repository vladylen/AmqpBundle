<?php
namespace M6Web\Bundle\AmqpBundle\Composer;

use Sensio\Bundle\DistributionBundle\Composer\ScriptHandler as SensioScriptHandler;
use Composer\Script\CommandEvent;

/**
 * @author Warnar Boekkooi <warnar@boekkooi.net>
 */
class ScriptHandler extends SensioScriptHandler
{
    /**
     * Declare AMQP exchanges and queues
     *
     * @param $event CommandEvent A instance
     */
    public static function AMQPdeclare(CommandEvent $event)
    {
        $options = self::getOptions($event);
        $appDir = $options['symfony-app-dir'];

        if (!is_dir($appDir)) {
            echo 'The symfony-app-dir ('.$appDir.') specified in composer.json was not found in '.getcwd().', can not clear the cache.'.PHP_EOL;

            return;
        }

        self::executeCommand($event, $appDir, 'm6web:amqp:declare', $options['process-timeout']);
    }
}
