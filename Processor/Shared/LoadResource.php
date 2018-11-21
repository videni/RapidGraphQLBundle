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
use App\Bundle\RestBundle\Metadata\Resource\ResourceMetadata;
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
        $resource = $this->load($context->getRequest(), $context->getOperationName(), $context->getClassName(), $context->getMetadata());

        $context->setResult($resource);
    }

    public function load(Request $request, $operationName, $className, ResourceMetadata $resourceMetadata)
    {
        $repositoryConfiguration = $resourceMetadata->getOperationAttribute($operationName, 'repository', [], true);

        $repositoryInstance = null;

        if (isset($repositoryConfiguration['id'])) {
            $repositoryInstance = $this->container->get($repositoryConfiguration['id']);
        } else {
            $repositoryServiceId = self::getRepositoryServiceId($resourceMetadata->getShortName());

            $repositoryInstance = $this->container->has($repositoryServiceId)? $this->container->get($repositoryServiceId): $this->registry->getRepository($className);
        }

        if (isset($repositoryConfiguration['method'])) {
            $method = $repositoryConfiguration['method'];
            $arguments = isset($factoryConfigurations['arguments'])? $factoryConfigurations['arguments']: [];

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
