<?php

namespace DivLooper\ElasticAPMBundle\EventListener;

use DivLooper\ElasticAPMBundle\ElasticAPMAgent;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
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
        $command = $event->getCommand();

        $this->apm->agent->startTransaction($command->getName());
    }

    /**
     * @param ConsoleTerminateEvent $event
     * @throws \PhilKra\Exception\Transaction\UnknownTransactionException
     */
    public function onConsoleTerminate(ConsoleTerminateEvent $event)
    {
        $command = $event->getCommand();

        $this->apm->agent->stopTransaction($command->getName());
        $this->apm->agent->send();
    }
}
