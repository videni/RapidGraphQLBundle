<?php

namespace Videni\Bundle\RapidGraphQLBundle\GraphQL\Resolver;

use Overblog\GraphQLBundle\Definition\Argument;
use Symfony\Component\HttpFoundation\Request;
use Videni\Bundle\RapidGraphQLBundle\GraphQL\Resolver\DataPersister;
use Overblog\GraphQLBundle\Definition\Resolver\MutationInterface;
use Videni\Bundle\RapidGraphQLBundle\Controller\ControllerResolver;

class Delete extends AbstractResolver implements MutationInterface
{
    private $dataPersister;

    public function __construct(
        ResourceContextResolver $resourceContextResolver,
        ControllerResolver $controllerResolver,
        ControllerExecutor $controllerExecutor,
        DataPersister $dataPersister
    ) {
        parent::__construct($resourceContextResolver, $controllerResolver, $controllerExecutor);

        $this->dataPersister = $dataPersister;
    }

    public function __invoke(Argument $args, $operationName, $actionName, Request $request)
    {
        $request->attributes->set('arguments', $args);

        $context = $this->resourceContextResolver->resolveResourceContext($operationName, $actionName);
        $resource = $this->resourceContextResolver->resolveResource($args, $context, $request);
        $resource = $this->controllerExecutor->execute($context, $request);

        $this->dataPersister->remove($resource);

        if (false === $controller = $this->controllerResolver->getController($context)) {
            return null;
        }

        return $this->controllerExecutor->execute($controller, $request);
    }
}
