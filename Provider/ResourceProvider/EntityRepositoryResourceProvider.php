<?php

declare(strict_types=1);

namespace Videni\Bundle\RestBundle\Provider\ResourceProvider;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Videni\Bundle\RestBundle\Context\ResourceContext;
use Videni\Bundle\RestBundle\Operation\ActionTypes;
use Symfony\Component\HttpFoundation\Request;
use Videni\Bundle\RestBundle\Config\Resource\Service;

class EntityRepositoryResourceProvider extends AbstractResourceProvider
{
    public function supports(ResourceContext $context, Request $request)
    {
        return in_array($context->getAction(), [ActionTypes::VIEW, ActionTypes::UPDATE, ActionTypes::DELETE]);
    }

    /**
     * {@inheritdoc}
     */
    public function getResource(ResourceContext $context, Request $request)
    {
        $result = parent::getResource($context, $request);

        if (null === $result) {
            throw new NotFoundHttpException('The resource you requested is not found');
        }

        return $result;
    }

    public function getMethod(Service $providerConfig): string
    {
       return  $providerConfig->getMethod() ?? 'find';
    }

    protected function getArguments(Request $request, Service $providerConfig): array
    {
        $arguments = parent::getArguments($request, $providerConfig);
        if (empty($arguments) && $request->attributes->has('id')) {
            $id = $request->attributes->get('id');

            return ['id' => $id];
        }

        return $arguments;
    }
}
