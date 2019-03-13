<?php

declare(strict_types=1);

namespace Videni\Bundle\RestBundle\Provider\ResourceProvider;

use Symfony\Component\HttpFoundation\Request;
use Videni\Bundle\RestBundle\Context\ResourceContext;

interface ResourceProviderInterface
{
    public function supports(ResourceContext $context, Request $request);

    public function getResource(ResourceContext $context, Request $request);
}
