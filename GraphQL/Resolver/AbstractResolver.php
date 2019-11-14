<?php

namespace Videni\Bundle\RapidGraphQLBundle\GraphQL\Resolver;

use Videni\Bundle\RapidGraphQLBundle\Controller\ControllerResolver;

abstract class AbstractResolver {

    protected $resourceContextResolver;
    protected $controllerExecutor;
    protected $controllerResolver;

    public function __construct(
        ResourceContextResolver $resourceContextResolver,
        ControllerResolver $controllerResolver,
        ControllerExecutor $controllerExecutor
    ) {
        $this->resourceContextResolver = $resourceContextResolver;
        $this->controllerResolver = $controllerResolver;
        $this->controllerExecutor = $controllerExecutor;
    }
}
