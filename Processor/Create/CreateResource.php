<?php

namespace Videni\Bundle\RestBundle\Processor\Create;

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
use Doctrine\Common\Inflector\Inflector;
use Videni\Bundle\RestBundle\Factory\Factory;
use Videni\Bundle\RestBundle\Config\Resource\ResourceConfig;
use Videni\Bundle\RestBundle\Config\Resource\ServiceConfig;

class CreateResource implements ProcessorInterface
{
    private $container;

    private $parametersParser;

    public function __construct(ContainerInterface $container, ParametersParserInterface $parametersParser)
    {
        $this->container = $container;
        $this->parametersParser = $parametersParser;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContextInterface $context)
    {
        $resource = $this->create($context->getRequest(), $context->getOperationName(), $context->getClassName(), $context->getResourceConfig());

        $context->setResult($resource);
    }

    /**
     * {@inheritdoc}
     */
    public function create(Request $request, $operationName, $className, ResourceConfig $resourceConfig)
    {
        /** @var ServiceConfig  */
        $factoryConfig = $resourceConfig->getOperationAttribute($operationName, 'factory');
        if (null === $factoryConfig) {
            throw new \RuntimeException(sprintf('No resource factory found for class %s', $className));
        }
        $factoryInstance = $this->container->get($factoryConfig->getId());

        $method = $factoryConfig->getMethod()?? 'createNew';

        $arguments = $factoryConfig->getArguments();
        if (!is_array($arguments)) {
            $arguments = [$arguments];
        }

        $arguments = array_values($this->parametersParser->parseRequestValues($arguments, $request));

        return $factoryInstance->$method(...$arguments);
    }
}
