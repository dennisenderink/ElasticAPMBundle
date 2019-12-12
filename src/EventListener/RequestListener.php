<?php

namespace DivLooper\ElasticAPMBundle\EventListener;

use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;
use DivLooper\ElasticAPMBundle\ElasticAPMAgent;

/**
 * Class RequestListener
 * @package DivLooper\ElasticAPMBundle\EventListener
 */
class RequestListener
{
    /**
     * @var ElasticAPMAgent
     */
    private $apm;

    /**
     * RequestListener constructor.
     * @param ElasticAPMAgent $elasticAPMAgent
     */
    public function __construct(ElasticAPMAgent $elasticAPMAgent)
    {
        $this->apm = $elasticAPMAgent;
    }

    /**
     * @param GetResponseEvent $event
     * @throws \PhilKra\Exception\Transaction\DuplicateTransactionNameException
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        if (HttpKernel::MASTER_REQUEST != $event->getRequestType()) {
            return;
        }

        $routeName = $event->getRequest()->get('_route');
        $controllerName = $event->getRequest()->get('_controller');

        $this->apm->agent->startTransaction(sprintf('%s (%s)', $controllerName, $routeName));
    }

    /**
     * @param GetResponseForExceptionEvent $event
     */
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        // only handle master request exceptions
        if (HttpKernel::MASTER_REQUEST != $event->getRequestType()) {
            return;
        }

        // register exception
        $this->apm->agent->captureThrowable($event->getException());
    }

    /**
     * @param PostResponseEvent $event
     * @throws \PhilKra\Exception\Transaction\UnknownTransactionException
     */
    public function onKernelTerminate(PostResponseEvent $event)
    {
        if (HttpKernel::MASTER_REQUEST != $event->getRequestType()) {
            return;
        }

        $routeName = $event->getRequest()->get('_route');
        $controllerName = $event->getRequest()->get('_controller');

        $this->apm->agent->stopTransaction(sprintf('%s (%s)', $controllerName, $routeName));
        $this->apm->agent->send();
    }
}
