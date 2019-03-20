<?php

namespace Videni\Bundle\RestBundle\EventListener;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Videni\Bundle\RestBundle\Context\ResourceContextStorage;
use Videni\Bundle\RestBundle\Operation\ActionTypes;
use Videni\Bundle\RestBundle\Provider\ResourceProvider\ChainResourceProvider;

class ResourceProviderListener
{
    private $resourceContextStorage;
    private $resourceProvider;

    /**
     * @throws InvalidArgumentException
     */
    public function __construct(
        ResourceContextStorage $resourceContextStorage,
        ChainResourceProvider $resourceProvider
    ) {
        $this->resourceContextStorage = $resourceContextStorage;
        $this->resourceProvider = $resourceProvider;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        if(!$event->isMasterRequest()) {
            return;
        }

        $resourceContext = $this->resourceContextStorage->getContext();
        if (null === $resourceContext) {
            return;
        }
        $request = $event->getRequest();

        if(!$request->attributes->get('_api_receive', false)) {
            return;
        }

        $data = $this->resourceProvider->getResource($resourceContext, $request);

        $request->attributes->set('data', $data);
    }
}
