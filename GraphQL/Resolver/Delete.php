<?php

namespace Videni\Bundle\RapidGraphQLBundle\GraphQL\Resolver;

use Videni\Bundle\RapidGraphQLBundle\Definition\Argument;
use Videni\Bundle\RapidGraphQLBundle\GraphQL\Resolver\DataPersister;
use Overblog\GraphQLBundle\Definition\Resolver\MutationInterface;
use Videni\Bundle\RapidGraphQLBundle\Controller\ControllerResolver;
use Videni\Bundle\RapidGraphQLBundle\Security\ResourceAccessCheckerInterface;

class Delete extends AbstractResolver implements MutationInterface
{
    private $dataPersister;

    public function __construct(
        ResourceContextResolver $resourceContextResolver,
        ControllerResolver $controllerResolver,
        ControllerExecutor $controllerExecutor,
        ResourceAccessCheckerInterface $resourceAccessChecker,
        DataPersister $dataPersister
    ) {
        parent::__construct($resourceContextResolver, $controllerResolver, $controllerExecutor, $resourceAccessChecker);

        $this->dataPersister = $dataPersister;
    }

    public function __invoke(Argument $args, $operationName, $actionName)
    {
        $context = $this->resourceContextResolver->resolveResourceContext($operationName, $actionName);
        $resource = $this->resourceContextResolver->resolveResource($args, $context);

        $this->checkPermission($resource, $context->getAction(), $args);

        $controller = $this->controllerResolver->getController($context);

        return $this->controllerExecutor->execute($controller, $args);
    }
}
