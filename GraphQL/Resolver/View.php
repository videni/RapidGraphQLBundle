<?php

namespace Videni\Bundle\RapidGraphQLBundle\GraphQL\Resolver;

use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;
use Symfony\Component\HttpFoundation\Request;

class View implements ResolverInterface
{
    private $resourceContextResolver;
    private $controllerExecutor;

    public function __construct(
        ResourceContextResolver $resourceContextResolver,
        ControllerExecutor $controllerExecutor
    ) {
        $this->resourceContextResolver = $resourceContextResolver;
        $this->controllerExecutor = $controllerExecutor;
    }

    public function __invoke(Argument $args, $operationName, $actionName, Request $request)
    {
        $context = $this->resourceContextResolver->resolveResourceContext($operationName, $actionName);

        $resource = $this->resourceContextResolver->resolveResource($args, $context, $request);
        $resource = $this->controllerExecutor->execute($context, $request);

        $request->attributes->set('data', $resource);

        return $resource;
    }
}
