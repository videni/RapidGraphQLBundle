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
    private $controllerExecutor;

    public function __construct(
        ResourceContextResolver $resourceContextResolver,
        DataPersister $dataPersister,
        ControllerExecutor $controllerExecutor
    ) {
        $this->dataPersister = $dataPersister;
        $this->resourceContextResolver = $resourceContextResolver;
        $this->controllerExecutor = $controllerExecutor;
    }

    public function __invoke(Argument $args, $operationName, $actionName, Request $request)
    {
        $context = $this->resourceContextResolver->resolveResourceContext($operationName, $actionName);
        $resource = $this->resourceContextResolver->resolveResource($args, $context, $request);
        $resource = $this->controllerExecutor->execute($context, $request);

        $this->dataPersister->remove($resource);

        return null;
    }
}
