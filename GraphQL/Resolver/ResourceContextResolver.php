<?php

namespace Videni\Bundle\RapidGraphQLBundle\GraphQL\Resolver;

use Overblog\GraphQLBundle\Definition\Argument;
use Videni\Bundle\RapidGraphQLBundle\Context\ResourceContext;
use Videni\Bundle\RapidGraphQLBundle\Provider\ResourceProvider\ChainResourceProvider;
use Videni\Bundle\RapidGraphQLBundle\Config\Resource\ConfigProvider;

class ResourceContextResolver
{
    private $resourceFactory;
    private $resourceConfigProvider;

    public function __construct(
        ConfigProvider $resourceConfigProvider,
        ChainResourceProvider $resourceFactory
    ) {
        $this->resourceFactory = $resourceFactory;
        $this->resourceConfigProvider = $resourceConfigProvider;
    }

    public function resolveResource(Argument $args, ResourceContext $context)
    {
        $resource = $this->resourceFactory->getResource($context, function($parameterName) use($args) {
            if(isset($args[$parameterName])) {
                return $args[$parameterName];
            }

            if(isset($args['input'])) {
                return $args['input'][$parameterName];
            }

            return null;
        });

        return $resource;
    }

    public function resolveResourceContext($operationName, $actionName)
    {
        $operation = $this->resourceConfigProvider->getOperation($operationName);
        if(!$operation->hasAction($actionName)) {
            throw new \LogicException(sprintf('Action %s is not found for operation %s', $actionName, $operationName));
        }

        return  new ResourceContext(
            $operationName,
            $operation,
            $actionName,
            $operation->getAction($actionName),
            $this->resourceConfigProvider->getResource($operation->getResource())
        );
    }
}
