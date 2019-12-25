<?php

namespace Videni\Bundle\RapidGraphQLBundle\GraphQL\Resolver;

use Videni\Bundle\RapidGraphQLBundle\Definition\Argument;
use Videni\Bundle\RapidGraphQLBundle\GraphQL\Resolver\DataPersister;
use Overblog\GraphQLBundle\Definition\Resolver\MutationInterface;
use Videni\Bundle\RapidGraphQLBundle\Controller\ControllerResolver;
use Videni\Bundle\RapidGraphQLBundle\Security\ResourceAccessCheckerInterface;

class Create extends AbstractResolver implements MutationInterface
{
    private $formHandler;
    private $dataPersister;

    public function __construct(
        ResourceContextResolver $resourceContextResolver,
        ControllerResolver $controllerResolver,
        ControllerExecutor $controllerExecutor,
        ResourceAccessCheckerInterface $resourceAccessChecker,
        DataPersister $dataPersister,
        FormHandler $formHandler
    ) {
        parent::__construct($resourceContextResolver, $controllerResolver, $controllerExecutor, $resourceAccessChecker);

        $this->dataPersister = $dataPersister;
        $this->formHandler = $formHandler;
    }

    public function __invoke(Argument $args, $operationName, $actionName)
    {
        $context = $this->resourceContextResolver->resolveResourceContext($operationName, $actionName);

        $resource = $this->resourceContextResolver->resolveResource($args, $context);

        $this->checkPermission($resource, $context->getAction(), $args);

        $resource = $this->formHandler->handle(
            $resource,
            $context,
            isset($args['input']) ? $args['input']: $args->getArrayCopy(),
            $args
        );

        if (false === $controller = $this->controllerResolver->getController($context)) {
            return $resource;
        }

        return $this->controllerExecutor->execute($controller, $args);
    }
}
