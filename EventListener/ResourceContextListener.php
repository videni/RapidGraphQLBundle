<?php

namespace Videni\Bundle\RapidGraphQLBundle\EventListener;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Videni\Bundle\RapidGraphQLBundle\Config\Resource\ConfigProvider;
use Videni\Bundle\RapidGraphQLBundle\Operation\ActionTypes;
use Videni\Bundle\RapidGraphQLBundle\Context\ResourceContextStorage;
use Videni\Bundle\RapidGraphQLBundle\Context\ResourceContext;

class ResourceContextListener
{
    private $resourceContextStorage;
    private $resourceConfigProvider;

    public function __construct(
        ResourceContextStorage $resourceContextStorage,
        ConfigProvider $resourceConfigProvider
    ) {
        $this->resourceContextStorage = $resourceContextStorage;
        $this->resourceConfigProvider = $resourceConfigProvider;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        if (!$request->attributes->has('_api_operation_name')) {
            return;
        }

        $operationName = $request->attributes->get('_api_operation_name');

        $operation = $this->resourceConfigProvider->getOperation($operationName);

        $actionName = $request->attributes->get('_api_action_name');
        if(!$operation->hasAction($actionName)) {
            throw new \LogicException(sprintf('Action %s is not found for operation %s', $actionName, $operationName));
        }

        $resourceContext = new ResourceContext(
            $operationName,
            $operation,
            $actionName,
            $operation->getAction($actionName),
            $this->resourceConfigProvider->getResource($operation->getResource())
        );

        $this->resourceContextStorage->setContext($resourceContext);
    }
}
