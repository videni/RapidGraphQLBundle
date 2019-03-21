<?php

namespace Videni\Bundle\RestBundle\Provider\ResourceProvider;

use Videni\Bundle\RestBundle\Config\Resource\Service;
use Videni\Bundle\RestBundle\Context\ResourceContext;
use Videni\Bundle\RestBundle\Operation\ActionTypes;
use Symfony\Component\HttpFoundation\Request;
use Videni\Bundle\RestBundle\Factory\FactoryInterface;

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

    protected function getMethod($providerInstance, Service $providerConfig): string
    {
        $method =  $providerConfig->getMethod();
        if (!$method && $providerInstance instanceof FactoryInterface) {
            $method = 'createNew';
        }

        return $method;
    }
}
