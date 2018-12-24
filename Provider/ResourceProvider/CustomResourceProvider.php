<?php

declare(strict_types=1);

namespace Videni\Bundle\RestBundle\Provider\ResourceProvider;

use Symfony\Component\HttpFoundation\Request;
use Videni\Bundle\RestBundle\Context\ResourceContext;
use Symfony\Component\DependencyInjection\ContainerInterface;

class CustomResourceProvider implements ResourceProviderInterface
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;

    }

    public function get(ResourceContext $context, Request $request)
    {
        $data = null;
        $operationConfig = $context->getOperationConfig();

        $resourceProvider = $operationConfig->getResourceProvider();
        if(null == $resourceProvider) {
            return;
        }

        if($resourceProvider && $this->container->has($resourceProvider)) {
            $resourceProviderInstance = $this->container->get($resourceProvider);
            if($resourceProviderInstance instanceof ResourceProviderInterface) {
                $data = $resourceProviderInstance->get($context, $request);
            }
        }

        return $data;
    }
}
