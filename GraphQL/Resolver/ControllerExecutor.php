<?php

namespace Videni\Bundle\RapidGraphQLBundle\GraphQL\Resolver;

use Videni\Bundle\RapidGraphQLBundle\Controller\ArgumentResolverInterface;
use Videni\Bundle\RapidGraphQLBundle\Definition\Argument;

class ControllerExecutor
{
    private $argumentsResolver;

    public function __construct(ArgumentResolverInterface $argumentsResolver) {
        $this->argumentsResolver = $argumentsResolver;
    }

    public function execute($controller, Argument $argument)
    {
        // controller arguments
        $arguments = $this->argumentsResolver->getArguments($argument, $controller);

        return $controller(...$arguments);
    }
}
