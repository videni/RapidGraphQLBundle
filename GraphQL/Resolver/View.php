<?php

namespace Videni\Bundle\RapidGraphQLBundle\GraphQL\Resolver;

use Videni\Bundle\RapidGraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;

class View extends AbstractResolver implements ResolverInterface
{
    public function __invoke(Argument $args, $operationName, $actionName)
    {
        $context = $this->resourceContextResolver->resolveResourceContext($operationName, $actionName);

        $resource = $this->resourceContextResolver->resolveResource($args, $context);
        $args->attributes->set('data', $resource);

        $this->checkPermission($resource, $context->getAction(), $args);

        if (false === $controller = $this->controllerResolver->getController($context)) {
            return $resource;
        }

        return $this->controllerExecutor->execute($controller, $args);
    }
}
