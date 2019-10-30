<?php

namespace Videni\Bundle\RapidGraphQLBundle\GraphQL\Resolver;

use Overblog\GraphQLBundle\Definition\Argument;
use Symfony\Component\HttpFoundation\Request;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;

class FormSchema implements ResolverInterface
{
    private $resourceContextResolver;
    private $formHandler;
    private $controllerExecutor;

    public function __construct(
        ResourceContextResolver $resourceContextResolver,
        FormHandler $formHandler,
        ControllerExecutor $controllerExecutor
    ) {
        $this->resourceContextResolver = $resourceContextResolver;
        $this->formHandler = $formHandler;
        $this->controllerExecutor = $controllerExecutor;
    }

    public function __invoke(Argument $args, $operationName, $actionName, Request $request)
    {
        $context = $this->resourceContextResolver->resolveResourceContext($operationName, $actionName);

        $data = $this->resourceContextResolver->resolveResource($args, $context, $request);
        $data = $this->controllerExecutor->execute($context, $request);

        return $this->formHandler->generateFormSchema(
            $data,
            $context,
            $request
        );
    }
}
