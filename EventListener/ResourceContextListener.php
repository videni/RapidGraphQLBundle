<?php

namespace Videni\Bundle\RestBundle\EventListener;

use Videni\Bundle\RestBundle\Request\RestRequestHeaders;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Videni\Bundle\RestBundle\Config\Resource\ResourceConfigProvider;
use Videni\Bundle\RestBundle\Operation\ActionTypes;
use Videni\Bundle\RestBundle\Context\ResourceContextStorage;
use Videni\Bundle\RestBundle\Context\ResourceContext;

class ResourceContextListener
{
    private $resourceContextStorage;
    private $resourceConfigProvider;

    public function __construct(
        ResourceContextStorage $resourceContextStorage,
        ResourceConfigProvider $resourceConfigProvider
    ) {
        $this->resourceContextStorage = $resourceContextStorage;
        $this->resourceConfigProvider = $resourceConfigProvider;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        if (!$request->attributes->has('_api_resource_class')) {
            return;
        }

        $resourceContext = new ResourceContext();

        $entityClass = $request->attributes->get('_api_resource_class');

        $resourceConfig = $this->resourceConfigProvider->get($entityClass);
        $resourceContext->setResourceConfig($resourceConfig);

        $operationName = $request->attributes->get('_api_operation_name');
        $resourceContext->setOperationName($operationName);

        if(!$resourceConfig->hasOperation($operationName)) {
            throw new \LogicException(sprintf('Operation %s is not found for resource %s', $operationName, $entityClass));
        }

        $resourceContext->setAction($resourceConfig->getOperation($operationName)->getAction());

        $resourceContext->setClassName($entityClass);
        $resourceContext->setRequestHeaders(new RestRequestHeaders($request));


        $this->resourceContextStorage->setContext($resourceContext);
    }
}
