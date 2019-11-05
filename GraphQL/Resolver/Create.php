<?php

namespace Videni\Bundle\RapidGraphQLBundle\GraphQL\Resolver;

use Overblog\GraphQLBundle\Definition\Argument;
use Symfony\Component\HttpFoundation\Request;
use Videni\Bundle\RapidGraphQLBundle\GraphQL\Resolver\DataPersister;
use Overblog\GraphQLBundle\Definition\Resolver\MutationInterface;

class Create implements MutationInterface
{
    private $resourceContextResolver;
    private $controllerExecutor;
    private $dataPersister;
    private $formHandler;

    public function __construct(
        ResourceContextResolver $resourceContextResolver,
        DataPersister $dataPersister,
        FormHandler $formHandler,
        ControllerExecutor $controllerExecutor
    ) {
        $this->resourceContextResolver = $resourceContextResolver;
        $this->dataPersister = $dataPersister;
        $this->formHandler = $formHandler;
        $this->controllerExecutor = $controllerExecutor;
    }

    public function __invoke(Argument $args, $operationName, $actionName, Request $request)
    {
        $context = $this->resourceContextResolver->resolveResourceContext($operationName, $actionName);

        $resource = $this->resourceContextResolver->resolveResource($args, $context, $request);

        $resource = $this->formHandler->handle(
            $resource,
            $context,
            isset($args['input']) ? $args['input']: $args->getArrayCopy(),
            $request
        );

        $resource = $this->controllerExecutor->execute($context, $request);

        $this->dataPersister->persist($resource);

        return $resource;
    }
}
