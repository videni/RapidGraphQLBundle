<?php

namespace App\Bundle\RestBundle\Processor\Create;

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
use Doctrine\Common\Inflector\Inflector;
use App\Bundle\RestBundle\Factory\Factory;
use App\Bundle\RestBundle\Config\Resource\ResourceConfig;
use App\Bundle\RestBundle\Config\Resource\ServiceConfig;

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
        var_dump($resourceConfig);exit;
        if (null === $factoryConfig) {
            throw new \RuntimeException(sprintf('No resource factory found for class %s', $className));
        }

        $factoryInstance = $this->container->get($factoryConfig->getId());

        $method = $factoryConfig->has('method')? $factoryConfig->get('method'): 'createNew';

        $arguments = $factoryConfig->has('arguments')? $factoryConfig->getArguments(): [];
        if (!is_array($arguments)) {
            $arguments = [$arguments];
        }

        $arguments = array_values($this->parametersParser->parseRequestValues($arguments, $request));

        return $factoryInstance->$method(...$arguments);
    }
}
