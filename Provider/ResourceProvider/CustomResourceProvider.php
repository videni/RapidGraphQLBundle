<?php

declare(strict_types=1);

namespace Videni\Bundle\RestBundle\Provider\ResourceProvider;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Videni\Bundle\RestBundle\Context\ResourceContext;
use Videni\Bundle\RestBundle\ExpressionLanguage\ExpressionLanguage;

class CustomResourceProvider implements ResourceProviderInterface
{
    private $container;
    private $expression;

    public function __construct(ContainerInterface $container, ExpressionLanguage $expression)
    {
        $this->container = $container;
        $this->expression = $expression;
    }

    public function get(ResourceContext $context, Request $request)
    {
        $data = null;
        $operationConfig = $context->getOperationConfig();

        $resourceProvider = $operationConfig->getResourceProvider();
        if(null == $resourceProvider) {
            return;
        }

        if (0 === strpos($resourceProvider, 'expr:')) {
           return $this->expression->evaluate(substr($resourceProvider, 5), [
                'container' => $this->container
           ]);
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
