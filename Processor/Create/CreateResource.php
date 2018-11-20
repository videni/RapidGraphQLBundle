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
use App\Bundle\RestBundle\Metadata\Resource\ResourceMetadata;
use Doctrine\Common\Inflector\Inflector;
use App\Bundle\RestBundle\Factory\Factory;

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
        $resource = $this->create($context->getRequest(), $context->getOperationName(), $context->getClassName(), $context->getMetadata());

        $context->set('new_resource', $resource);
    }

    /**
     * {@inheritdoc}
     */
    public function create(Request $request, $operationName, $className, ResourceMetadata $resourceMetadata)
    {
        $factoryConfigurations = $resourceMetadata->getOperationAttribute($operationName, 'factory', [], true);
        $factoryInstance = null;

        if (isset($factoryConfigurations['id'])) {
            $factoryInstance = $this->container->get($factoryConfigurations['id']);
        } else {
            $factoryServiceId = self::getFactoryServiceId($resourceMetadata->getShortName());
            $factoryInstance =  $this->container->has($factoryServiceId) ? $this->container->get($factoryServiceId):  new Factory($className);
        }

        $method = isset($factoryConfigurations['method'])? $factoryConfigurations['method']: 'createNew';

        $arguments = isset($factoryConfigurations['arguments'])? $factoryConfigurations['arguments']: [];
        if (!is_array($arguments)) {
            $arguments = [$arguments];
        }

        $arguments = array_values($this->parametersParser->parseRequestValues($arguments, $request));

        return $factoryInstance->$method(...$arguments);
    }


    private static function getFactoryServiceId($resourceShortName)
    {
         $name = Inflector::tableize($resourceShortName);

         return sprintf('app_rest.factory.%s', $name);
    }
}
