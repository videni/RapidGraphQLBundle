<?php

declare(strict_types=1);

namespace Videni\Bundle\RestBundle\Processor\Shared;

use Videni\Bundle\RestBundle\Processor\SingleItemContext;
use Oro\Component\ChainProcessor\ContextInterface;
use Oro\Component\ChainProcessor\ProcessorInterface;
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

final class LoadResource implements ProcessorInterface
{
    private $container;

    private $parametersParser;

    private $registry;

    public function __construct(ContainerInterface $container, ParametersParserInterface $parametersParser, ManagerRegistry $registry)
    {
        $this->container = $container;
        $this->parametersParser = $parametersParser;
        $this->registry = $registry;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContextInterface $context)
    {
        $resource = $this->load($context->getRequest(), $context->getOperationName(), $context->getClassName(), $context->getResourceConfig());

        $context->setResult($resource);
    }

    public function load(Request $request, $operationName, $className, ResourceConfig $resourceConfig)
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

         return sprintf('videni_rest.repository.%s', $name);
    }
}
