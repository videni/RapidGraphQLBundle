<?php

declare(strict_types=1);

namespace Videni\Bundle\RestBundle\Provider\ResourceProvider;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Videni\Bundle\RestBundle\Context\ResourceContext;
use Videni\Bundle\RestBundle\Operation\ActionTypes;
use Symfony\Component\HttpFoundation\Request;
use Videni\Bundle\RestBundle\Config\Resource\Service;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

class EntityRepositoryResourceProvider extends AbstractResourceProvider
{
    public function supports(ResourceContext $context, Request $request)
    {
        return in_array($context->getActionType(), [ActionTypes::VIEW, ActionTypes::UPDATE, ActionTypes::DELETE]);
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

        if ($result instanceof QueryBuilder) {
            return $result->getQuery()->getResult();
        }

        return $result;
    }

    protected function getMethod($providerInstance, Service $providerConfig): string
    {
        $method =  $providerConfig->getMethod();
        if (!$method && $providerInstance instanceof EntityRepository) {
            $method = 'find';
        }

        return  $method;
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
