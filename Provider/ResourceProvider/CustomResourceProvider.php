<?php

namespace Videni\Bundle\RestBundle\Provider\ResourceProvider;

use Videni\Bundle\RestBundle\Config\Resource\Service;
use Videni\Bundle\RestBundle\Context\ResourceContext;
use Videni\Bundle\RestBundle\Operation\ActionTypes;
use Symfony\Component\HttpFoundation\Request;

class CustomResourceProvider extends AbstractResourceProvider
{
    public function supports(ResourceContext $context, Request $request)
    {
        return true;
    }
}
