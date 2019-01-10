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
        if(!$event->isMasterRequest()) {
            return;
        }

        $request = $event->getRequest();
        if (!$request->attributes->has('_api_resource_class')) {
            return;
        }

        $resourceContext = new ResourceContext();

        $entityClass = $request->attributes->get('_api_resource_class');
        $action = $request->attributes->get('_action');

        $resourceContext->setClassName($entityClass);
        $resourceContext->setOperationName($request->attributes->get('_api_operation_name'));
        $resourceContext->setRequestHeaders(new RestRequestHeaders($request));
        $resourceContext->setAction($action);

        $resourceConfig = $this->resourceConfigProvider->get($entityClass);
        $resourceContext->setResourceConfig($resourceConfig);

        $this->resourceContextStorage->setContext($resourceContext);
    }
}
