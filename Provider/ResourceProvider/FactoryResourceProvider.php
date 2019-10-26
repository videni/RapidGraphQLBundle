<?php

namespace Videni\Bundle\RapidGraphQLBundle\Provider\ResourceProvider;

use Videni\Bundle\RapidGraphQLBundle\Config\Resource\Service;
use Videni\Bundle\RapidGraphQLBundle\Context\ResourceContext;
use Videni\Bundle\RapidGraphQLBundle\Operation\ActionTypes;
use Videni\Bundle\RapidGraphQLBundle\Factory\FactoryInterface;

class FactoryResourceProvider extends AbstractResourceProvider
{
    public function supports(ResourceContext $context)
    {
        return $context->getActionType() === ActionTypes::CREATE;
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
