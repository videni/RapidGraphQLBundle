<?php

namespace Videni\Bundle\RapidGraphQLBundle\EventListener;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Videni\Bundle\RapidGraphQLBundle\Context\ResourceContextStorage;
use Videni\Bundle\RapidGraphQLBundle\Operation\ActionTypes;
use Videni\Bundle\RapidGraphQLBundle\Provider\ResourceProvider\ChainResourceProvider;
use Videni\Bundle\RapidGraphQLBundle\Event\EventDispatcher;
use Videni\Bundle\RapidGraphQLBundle\Event\ResolveResourceFormEvent;

class ResourceProviderListener
{
    private $resourceContextStorage;
    private $resourceProvider;
    private $eventDispatcher;

    /**
     * @throws InvalidArgumentException
     */
    public function __construct(
        ResourceContextStorage $resourceContextStorage,
        ChainResourceProvider $resourceProvider,
        EventDispatcher $eventDispatcher
    ) {
        $this->resourceContextStorage = $resourceContextStorage;
        $this->resourceProvider = $resourceProvider;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        if(!$event->isMasterRequest()) {
            return;
        }

        $context = $this->resourceContextStorage->getContext();
        if (null === $context) {
            return;
        }

        $request = $event->getRequest();
        if ($request->attributes->get('_api_receive', true)) {

            $data = $this->resourceProvider->getResource($context, $request);
            $request->attributes->set('data', $data);

            $this->eventDispatcher->dispatchResourcePostResolveEvent(
                implode('_', [$context->getOperationName(), $context->getActionName()]),
                $context->getResource(),
                $data
            );
        }
    }
}
