<?php

namespace Videni\Bundle\RapidGraphQLBundle\GraphQL\Resolver;

use Overblog\GraphQLBundle\Definition\Argument;
use Symfony\Component\HttpFoundation\Request;
use Videni\Bundle\RapidGraphQLBundle\GraphQL\Resolver\DataPersister;
use Overblog\GraphQLBundle\Definition\Resolver\MutationInterface;

class Delete implements MutationInterface
{
    private $dataPersister;
    private $resourceContextResolver;

    public function __construct(
        ResourceContextResolver $resourceContextResolver,
        DataPersister $dataPersister
    ) {
        $this->dataPersister = $dataPersister;
        $this->resourceContextResolver = $resourceContextResolver;
    }

    public function __invoke(Argument $args, $operationName, $actionName, Request $request)
    {
        $context = $this->resourceContextResolver->resolveResourceContext($operationName, $actionName);

        $resource = $this->resourceContextResolver->resolveResource($args, $context);

        $this->dataPersister->remove($resource);

        return null;
    }
}
