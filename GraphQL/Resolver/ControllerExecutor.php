<?php

namespace Videni\Bundle\RapidGraphQLBundle\GraphQL\Resolver;

use Symfony\Component\HttpFoundation\Request;
use Videni\Bundle\RapidGraphQLBundle\Context\ResourceContext;
use Symfony\Component\HttpKernel\Controller\ArgumentResolverInterface;
use Videni\Bundle\RapidGraphQLBundle\Controller\ControllerResolver;

class ControllerExecutor
{
    private $controllerResolver;
    private $argumentsResolver;

    public function __construct(
        ControllerResolver $controllerResolver,
        ArgumentResolverInterface $argumentsResolver
    ) {
        $this->controllerResolver = $controllerResolver;
        $this->argumentsResolver = $argumentsResolver;
    }

    public function execute(ResourceContext $resourceContext, Request $request)
    {
        if (false === $controller = $this->controllerResolver->getController($resourceContext)) {
            return $request->attributes->get('data');
        }

        // controller arguments
        $arguments = $this->argumentResolver->getArguments($request, $controller);

        return $controller(...$arguments);
    }
}
