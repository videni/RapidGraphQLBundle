<?php

declare(strict_types=1);

namespace Videni\Bundle\RapidGraphQLBundle\Provider\ResourceProvider;

use Videni\Bundle\RapidGraphQLBundle\Context\ResourceContext;
use Videni\Bundle\RapidGraphQLBundle\Factory\ParametersParserInterface;

class ExpressionResourceProvider implements ResourceProviderInterface
{
    private $parser;

    public function __construct(ParametersParserInterface $parser)
    {
        $this->parser = $parser;
    }

    public function supports(ResourceContext $context)
    {
        $providerConfig = $context->getAction()->getResourceProvider();

        return 0 === strpos($providerConfig->getId(), 'expr:');
    }

    public function getResource(ResourceContext $context, callable $getter)
    {
        $providerConfig = $context->getAction()->getResourceProvider();
        $id = $providerConfig->getId();

        return $this->parser->parseRequestValueExpression(substr($id, 5), $getter);
    }
}
