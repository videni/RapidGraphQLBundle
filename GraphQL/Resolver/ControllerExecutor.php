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

        var_dump($request->attributes->get('form'));exit;
        // controller arguments
        $arguments = $this->argumentsResolver->getArguments($request, $controller);

        return $controller(...$arguments);
    }
}
