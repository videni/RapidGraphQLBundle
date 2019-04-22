<?php

namespace Videni\Bundle\RestBundle\EventListener;

use Videni\Bundle\RestBundle\Context\ResourceContextStorage;
use Videni\Bundle\RestBundle\Operation\ActionTypes;
use Videni\Bundle\RestBundle\Event\EventDispatcher;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;

class WriteListener
{
    private $resourceContextStorage;
    private $eventDispatcher;
    private $dataPersister;

    /**
     * @throws InvalidArgumentException
     */
    public function __construct(
        ResourceContextStorage $resourceContextStorage,
        EventDispatcher $eventDispatcher,
        DataPersister $dataPersister
    ) {
        $this->resourceContextStorage = $resourceContextStorage;
        $this->eventDispatcher = $eventDispatcher;
        $this->dataPersister = $dataPersister;
    }

    public function onKernelView(GetResponseForControllerResultEvent $event)
    {
        $request = $event->getRequest();
        if ($request->isMethodSafe(false) || !$request->attributes->has('_api_operation_class') || !$request->attributes->getBoolean('_api_persist', true)) {
            return;
        }

        $data = $request->attributes->get('data');

        $controllerResult = $event->getControllerResult();

        $context = $this->resourceContextStorage->getContext();

        $actionType = $context->getActionType();

        $this->eventDispatcher->dispatchPreEvent($actionType, $context->getResource(), $data);

        if (in_array($actionType, [ActionTypes::UPDATE, ActionTypes::CREATE])) {
            $this->dataPersister->persist($controllerResult);
        }
        if (in_array($actionType, [ActionTypes::DELETE, ActionTypes::BULK_DELETE])) {
            $this->dataPersister->remove($data);
        }

        $this->eventDispatcher->dispatchPostEvent($actionType, $context->getResource(), $data);
    }
}
