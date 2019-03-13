<?php

declare(strict_types=1);

namespace Videni\Bundle\RestBundle\Provider\ResourceProvider;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Videni\Bundle\RestBundle\Context\ResourceContext;
use Videni\Bundle\RestBundle\ExpressionLanguage\ExpressionLanguage;

class ExpressionResourceProvider implements ResourceProviderInterface
{
    private $container;
    private $expression;

    public function __construct(ContainerInterface $container, ExpressionLanguage $expression)
    {
        $this->container = $container;
        $this->expression = $expression;
    }

    public function supports(ResourceContext $context,Request $request)
    {
        $providerConfig = $context->getOperationConfig()->getResourceProvider();

        return 0 === strpos($providerConfig->getId(), 'expr:');
    }

    public function getResource(ResourceContext $context, Request $request)
    {
        if (!$this->supports($context, $request)) {
            return null;
        }

        $providerConfig = $context->getOperationConfig()->getResourceProvider();
        $id = $providerConfig->getId();

        return $this->expression->evaluate(
            substr($id, 5), [
                'container' => $this->container
           ]
       );
    }
}
