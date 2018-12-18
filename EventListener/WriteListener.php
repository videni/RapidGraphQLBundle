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
        $data = $request->attributes->get('data');

        $request = $event->getRequest();
        if ($request->isMethodSafe(false) || !$request->attributes->has('_api_resource_class') || !$request->attributes->getBoolean('_api_persist', true)) {
            return;
        }

        $controllerResult = $event->getControllerResult();

        $context = $this->resourceContextStorage()->getContext();

        $action = $context->getAction();

        $this->eventDispatcher->dispatchPreEvent($context->getAction(), $context->getResourceConfig(), $entity);

        if (in_array($action, [ActionTypes::UPDATE, ActionTypes::CREATE])) {
            $this->dataPersister->persist($controllerResult);
        }
        if (in_array($action, [ActionTypes::DELETE, ActionTypes::BULK_DELETE])) {
            $this->dataPersister->remove($data);
        }

        $this->eventDispatcher->dispatchPostEvent($context->getAction(), $context->getResourceConfig(), $entity);
    }
}
