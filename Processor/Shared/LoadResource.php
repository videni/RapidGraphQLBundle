<?php

declare(strict_types=1);

namespace App\Bundle\RestBundle\Processor\Shared;

use App\Bundle\RestBundle\Processor\SingleItemContext;
use Oro\Component\ChainProcessor\ContextInterface;
use Oro\Component\ChainProcessor\ProcessorInterface;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectManager as DoctrineObjectManager;
use Doctrine\Common\Util\ClassUtils;
use App\Bundle\RestBundle\Exception;
use Symfony\Component\DependencyInjection\ContainerInterface;
use App\Bundle\RestBundle\Factory\ParametersParserInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Bundle\RestBundle\Config\Resource\ResourceConfig;
use App\Bundle\RestBundle\Config\Resource\ServiceConfig;
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

        $repositoryInstance = $this->container->get($repositoryConfig['id']);

        if ($method = $repositoryConfig->getMethod()) {
            $arguments = $repositoryConfig->getArguments() ?? [];
            if (!is_array($arguments)) {
                $arguments = [$arguments];
            }

            $arguments = array_values($this->parametersParser->parseRequestValues($arguments, $request));

            return $repositoryInstance->$method(...$arguments);
        }

        $criteria = [];

        if ($request->attributes->has('id')) {
            return $repositoryInstance->find($request->attributes->get('id'));
        }

        return null;
    }

    private static function getRepositoryServiceId($resourceShortName)
    {
         $name = Inflector::tableize($resourceShortName);

         return sprintf('app_rest.repository.%s', $name);
    }
}
