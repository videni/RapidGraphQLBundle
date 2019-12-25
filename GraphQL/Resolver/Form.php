<?php

namespace Videni\Bundle\RapidGraphQLBundle\GraphQL\Resolver;

use Videni\Bundle\RapidGraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;

class Form implements ResolverInterface
{
    private $resourceContextResolver;
    private $formHandler;

    public function __construct(
        ResourceContextResolver $resourceContextResolver,
        FormHandler $formHandler
    ) {
        $this->resourceContextResolver = $resourceContextResolver;
        $this->formHandler = $formHandler;
    }

    public function __invoke(Argument $args, $operationName, $actionName)
    {
        $context = $this->resourceContextResolver->resolveResourceContext($operationName, $actionName);

        $data = $this->resourceContextResolver->resolveResource($args, $context);

        return $this->formHandler->resolveForm(
            $context,
            $data,
            $args
        );
    }
}
