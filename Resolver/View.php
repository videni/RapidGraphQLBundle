<?php

namespace Videni\Bundle\RestBundle\Resolver;

use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;

class View implements ResolverInterface
{
    private $resourceContextResolver;

    public function __construct(ResourceContextResolver $resourceContextResolver)
    {
        $this->resourceContextResolver = $resourceContextResolver;
    }

    public function __invoke(Argument $args, $operationName, $actionName)
    {
        $context = $this->resourceContextResolver->resolveResourceContext($operationName, $actionName);

        return $this->resourceContextResolver->resolveResource($args, $context);
    }
}
