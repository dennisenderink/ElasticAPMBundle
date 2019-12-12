<?php

namespace DivLooper\ElasticAPMBundle\EventListener;

use DivLooper\ElasticAPMBundle\ElasticAPMAgent;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Event\ConsoleErrorEvent;
use Symfony\Component\Console\Event\ConsoleTerminateEvent;

/**
 * Class ConsoleListener
 * @package DivLooper\ElasticAPMBundle\EventListener
 */
class ConsoleListener
{
    /**
     * ConsoleListener constructor.
     * @param ElasticAPMAgent $elasticAPMAgent
     */
    public function __construct(ElasticAPMAgent $elasticAPMAgent)
    {
        $this->apm = $elasticAPMAgent;
    }

    /**
     * @param ConsoleCommandEvent $event
     * @throws \PhilKra\Exception\Transaction\DuplicateTransactionNameException
     */
    public function onConsoleCommand(ConsoleCommandEvent $event)
    {
        // get command
        $command = $event->getCommand();

        // start transaction
        $this->apm->agent->startTransaction($command->getName());
    }

    /**
     * @param ConsoleErrorEvent $event
     */
    public function onConsoleError(ConsoleErrorEvent $event)
    {
        // register exception
        $this->apm->agent->captureThrowable($event->getError());
    }

    /**
     * @param ConsoleTerminateEvent $event
     * @throws \PhilKra\Exception\Transaction\UnknownTransactionException
     */
    public function onConsoleTerminate(ConsoleTerminateEvent $event)
    {
        // get command
        $command = $event->getCommand();

        // stop transaction (retrieve meta data and so forth)
        $this->apm->agent->stopTransaction($command->getName());
        // send data to apm server
        $this->apm->agent->send();
    }
}
