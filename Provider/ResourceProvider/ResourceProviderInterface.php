<?php

declare(strict_types=1);

namespace Videni\Bundle\RestBundle\Provider\ResourceProvider;

use Videni\Bundle\RestBundle\Context\ResourceContext;

interface ResourceProviderInterface
{
    public function supports(ResourceContext $context);

    public function getResource(ResourceContext $context, callable $getter);
}
