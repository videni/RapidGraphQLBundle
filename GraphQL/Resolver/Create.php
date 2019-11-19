<?php

namespace Videni\Bundle\RapidGraphQLBundle\GraphQL\Resolver;

use Overblog\GraphQLBundle\Definition\Argument;
use Symfony\Component\HttpFoundation\Request;
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

    public function __invoke(Argument $args, $operationName, $actionName, Request $request)
    {
        $request->attributes->set('arguments', $args);

        $context = $this->resourceContextResolver->resolveResourceContext($operationName, $actionName);

        $resource = $this->resourceContextResolver->resolveResource($args, $context, $request);

        $this->checkPermission($resource, $context->getAction(), $request);

        $resource = $this->formHandler->handle(
            $resource,
            $context,
            isset($args['input']) ? $args['input']: $args->getArrayCopy(),
            $request
        );

        if (false === $controller = $this->controllerResolver->getController($context)) {
            return $resource;
        }

        return $this->controllerExecutor->execute($controller, $request);
    }
}
