<?php

namespace Videni\Bundle\RapidGraphQLBundle\GraphQL\Resolver;

use Overblog\GraphQLBundle\Definition\Argument;
use Symfony\Component\HttpFoundation\Request;
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

    public function __invoke(Argument $args, $operationName, $actionName, Request $request)
    {
        $request->attributes->set('arguments', $args);

        $context = $this->resourceContextResolver->resolveResourceContext($operationName, $actionName);
        $resource = $this->resourceContextResolver->resolveResource($args, $context, $request);

        $this->checkPermission($resource, $context->getAction(), $request);

        $controller = $this->controllerResolver->getController($context);

        return $this->controllerExecutor->execute($controller, $request);
    }
}
