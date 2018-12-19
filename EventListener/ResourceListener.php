<?php

namespace Videni\Bundle\RestBundle\EventListener;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Videni\Bundle\RestBundle\Context\ResourceContextStorage;
use Videni\Bundle\RestBundle\Provider\ResourceProvider\CollectionResourceProvider;
use Videni\Bundle\RestBundle\Provider\ResourceProvider\SingleResourceProvider;
use Videni\Bundle\RestBundle\Operation\ActionTypes;
use Videni\Bundle\RestBundle\Factory\NewResourceFactory;

class ResourceListener
{
    private $resourceContextStorage;
    private $singleResourceProvider;
    private $collectionResourceProvider;
    private $newResourceFactory;

    /**
     * @throws InvalidArgumentException
     */
    public function __construct(
        ResourceContextStorage $resourceContextStorage,
        SingleResourceProvider $singleResourceProvider,
        CollectionResourceProvider $collectionResourceProvider,
        NewResourceFactory $newResourceFactory
    ) {
        $this->resourceContextStorage = $resourceContextStorage;
        $this->singleResourceProvider = $singleResourceProvider;
        $this->collectionResourceProvider = $collectionResourceProvider;
        $this->newResourceFactory = $newResourceFactory;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        $resourceContext = $this->resourceContextStorage->getContext();
        if (null === $resourceContext) {
            return;
        }

        $request = $event->getRequest();
        $data = null;
        $action = $resourceContext->getAction();
        if (in_array($action, [ActionTypes::VIEW, ActionTypes::UPDATE, ActionTypes::DELETE])) {
            $data = $this->singleResourceProvider->get($resourceContext, $request);
        } else if (in_array($action, [ActionTypes::INDEX])) {
            $data = $this->collectionResourceProvider->get($resourceContext, $request);
        } else if($action === ActionTypes::CREATE) {
            $data = $this->newResourceFactory->create($resourceContext, $request);
        }

        $request->attributes->set('data', $data);
    }
}
