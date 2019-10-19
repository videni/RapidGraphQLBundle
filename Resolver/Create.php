<?php

namespace Videni\Bundle\RestBundle\Resolver;

use Overblog\GraphQLBundle\Definition\Argument;
use Symfony\Component\HttpFoundation\Request;
use Videni\Bundle\RestBundle\Resolver\DataPersister;
use Overblog\GraphQLBundle\Definition\Resolver\MutationInterface;

class Create implements MutationInterface
{
    private $resourceContextResolver;
    private $dataPersister;
    private $formHandler;

    public function __construct(
        ResourceContextResolver $resourceContextResolver,
        DataPersister $dataPersister,
        FormHandler $formHandler
    ) {
        $this->resourceContextResolver = $resourceContextResolver;
        $this->dataPersister = $dataPersister;
        $this->formHandler = $formHandler;
    }

    public function __invoke(Argument $args, $operationName, $actionName, Request $request)
    {
        $context = $this->resourceContextResolver->resolveResourceContext($operationName, $actionName);

        $data = $this->resourceContextResolver->resolveResource($args, $context);

        $resource = $this->formHandler->handle(
            $data,
            $context,
            isset($args['input']) ? $args['input']: $args->getArrayCopy(),
            $request
        );

        $this->dataPersister->persist($resource);

        return $resource;
    }
}
