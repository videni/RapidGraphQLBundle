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
use Videni\Bundle\RestBundle\Config\Resource\ResourceConfig;
use Videni\Bundle\RestBundle\Config\Resource\ServiceConfig;
use Doctrine\Common\Inflector\Inflector;
use Videni\Bundle\RestBundle\Context\ResourceContext;

class SingleResourceProvider
{
    private $container;

    private $parametersParser;

    private $registry;

    private $applicationName;

    public function __construct(
        ContainerInterface $container,
        ParametersParserInterface $parametersParser,
        ManagerRegistry $registry,
        $applicationName
    ) {
        $this->container = $container;
        $this->parametersParser = $parametersParser;
        $this->registry = $registry;
        $this->applicationName = $applicationName;
    }

    /**
     * {@inheritdoc}
     */
    public function get(ResourceContext $context, Request $request)
    {
        return $this->load($request, $context->getOperationName(), $context->getClassName(), $context->getResourceConfig());
    }

    protected function load(Request $request, $operationName, $className, ResourceConfig $resourceConfig)
    {
        /** @var ServiceConfig */
        $repositoryConfig = $resourceConfig->getOperationAttribute($operationName, 'repository');

        $repositoryInstance = $this->container->get($repositoryConfig->getId());

        if ($method = $repositoryConfig->getMethod()) {
            $arguments = $repositoryConfig->getArguments() ?? [];
            if (!is_array($arguments)) {
                $arguments = [$arguments];
            }

            $arguments = array_values($this->parametersParser->parseRequestValues($arguments, $request));

            return $repositoryInstance->$method(...$arguments);
        }

        if ($request->attributes->has('id')) {
            return $repositoryInstance->find($request->attributes->get('id'));
        }

        return null;
    }

    private static function getRepositoryServiceId($resourceShortName)
    {
         $name = Inflector::tableize($resourceShortName);

         return sprintf('%s.repository.%s', $this->applicationName, $name);
    }
}