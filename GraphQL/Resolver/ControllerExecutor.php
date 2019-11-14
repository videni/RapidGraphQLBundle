<?php

namespace Videni\Bundle\RapidGraphQLBundle\GraphQL\Resolver;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentResolverInterface;

class ControllerExecutor
{
    private $argumentsResolver;

    public function __construct(ArgumentResolverInterface $argumentsResolver) {
        $this->argumentsResolver = $argumentsResolver;
    }

    public function execute($controller, Request $request)
    {
        // controller arguments
        $arguments = $this->argumentsResolver->getArguments($request, $controller);

        return $controller(...$arguments);
    }
}
