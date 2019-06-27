<?php

declare(strict_types=1);

namespace Videni\Bundle\RestBundle\Provider\ResourceProvider;

use Symfony\Component\HttpFoundation\Request;
use Videni\Bundle\RestBundle\Context\ResourceContext;
use Videni\Bundle\RestBundle\Factory\ParametersParserInterface;

class ExpressionResourceProvider implements ResourceProviderInterface
{
    private $parser;

    public function __construct(ParametersParserInterface $parser)
    {
        $this->parser = $parser;
    }

    public function supports(ResourceContext $context, Request $request)
    {
        $providerConfig = $context->getAction()->getResourceProvider();

        return 0 === strpos($providerConfig->getId(), 'expr:');
    }

    public function getResource(ResourceContext $context, Request $request)
    {
        $providerConfig = $context->getAction()->getResourceProvider();
        $id = $providerConfig->getId();

        return $this->parser->parseRequestValueExpression(substr($id, 5), $request);
    }
}
