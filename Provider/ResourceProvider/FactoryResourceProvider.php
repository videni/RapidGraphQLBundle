<?php

namespace Videni\Bundle\RestBundle\Provider\ResourceProvider;

use Videni\Bundle\RestBundle\Config\Resource\Service;
use Videni\Bundle\RestBundle\Context\ResourceContext;
use Videni\Bundle\RestBundle\Operation\ActionTypes;
use Symfony\Component\HttpFoundation\Request;

class FactoryResourceProvider extends AbstractResourceProvider
{
    public function supports(ResourceContext $context, Request $request)
    {
        $force = $request->attributes->get('_treat_as_new', false);
        if ($force) {
            return true;
        }

        return $context->getAction() === ActionTypes::CREATE;
    }

    public function getMethod(Service $providerConfig): string
    {
       return  $providerConfig->getMethod() ?? 'createNew';
    }
}
