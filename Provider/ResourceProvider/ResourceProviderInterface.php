<?php

declare(strict_types=1);

namespace Videni\Bundle\RapidGraphQLBundle\Provider\ResourceProvider;

use Videni\Bundle\RapidGraphQLBundle\Context\ResourceContext;

interface ResourceProviderInterface
{
    public function supports(ResourceContext $context);

    public function getResource(ResourceContext $context, callable $getter);
}
