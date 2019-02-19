<?php

declare(strict_types=1);

namespace Videni\Bundle\RestBundle\Provider\ResourceProvider;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectManager as DoctrineObjectManager;
use Doctrine\Common\Util\ClassUtils;
use Videni\Bundle\RestBundle\Exception;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Videni\Bundle\RestBundle\Factory\ParametersParserInterface;
use Symfony\Component\HttpFoundation\Request;
use Videni\Bundle\RestBundle\Config\Resource\Resource;
use Videni\Bundle\RestBundle\Config\Resource\Service;
use Doctrine\Common\Inflector\Inflector;
use Videni\Bundle\RestBundle\Context\ResourceContext;
use Videni\Bundle\RestBundle\Operation\ActionTypes;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SingleResourceProvider implements ResourceProviderInterface
{
    private $container;

    private $parametersParser;

    private $registry;

    public function __construct(
        ContainerInterface $container,
        ParametersParserInterface $parametersParser,
        ManagerRegistry $registry
    ) {
        $this->container = $container;
        $this->parametersParser = $parametersParser;
        $this->registry = $registry;
    }

    /**
     * {@inheritdoc}
     */
    public function get(ResourceContext $context, Request $request)
    {
        if (!in_array($context->getAction(), [ActionTypes::VIEW, ActionTypes::UPDATE, ActionTypes::DELETE])) {
            return;
        }

        return $this->load($request, $context->getOperationConfig()->getRepository(), $context->getClassName(), $context->getResourceConfig());
    }

    protected function load(Request $request, Service $repositoryConfig, $className, Resource $resourceConfig)
    {
        $repositoryInstance = $this->container->get($repositoryConfig->getId());

        $result = null;
        if ($method = $repositoryConfig->getMethod()) {
            $arguments = $repositoryConfig->getArguments() ?? [];
            if (!is_array($arguments)) {
                $arguments = [$arguments];
            }

            $arguments = array_values($this->parametersParser->parseRequestValues($arguments, $request));

            $result=  $repositoryInstance->$method(...$arguments);
        }

        $id = $request->attributes->get('id', null);
        if (null !== $id ) {
            $result =  $repositoryInstance->find($id);
        }

        if (null === $result) {
            throw new NotFoundHttpException('The resource you requested is not found');
        }

        return $result;
    }
}
