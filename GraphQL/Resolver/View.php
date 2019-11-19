<?php

namespace Videni\Bundle\RapidGraphQLBundle\GraphQL\Resolver;

use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;
use Symfony\Component\HttpFoundation\Request;

class View extends AbstractResolver implements ResolverInterface
{
    public function __invoke(Argument $args, $operationName, $actionName, Request $request)
    {
        $request->attributes->set('arguments', $args);

        $context = $this->resourceContextResolver->resolveResourceContext($operationName, $actionName);

        $resource = $this->resourceContextResolver->resolveResource($args, $context, $request);

        $this->checkPermission($resource, $context->getAction(), $request);

        $request->attributes->set('data', $resource);

        if (false === $controller = $this->controllerResolver->getController($context)) {
            return $resource;
        }

        return $this->controllerExecutor->execute($controller, $request);
    }
}
