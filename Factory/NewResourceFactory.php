<?php

namespace Videni\Bundle\RestBundle\Factory;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Videni\Bundle\RestBundle\Factory\ParametersParserInterface;
use Symfony\Component\HttpFoundation\Request;
use Videni\Bundle\RestBundle\Config\Resource\ResourceConfig;
use Videni\Bundle\RestBundle\Config\Resource\ServiceConfig;
use Videni\Bundle\RestBundle\Context\ResourceContext;

class NewResourceFactory
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
    public function create(ResourceContext $context, Request $request)
    {
        $resourceConfig = $context->getResourceConfig();

        /** @var ServiceConfig  */
        $factoryConfig = $resourceConfig->getOperationAttribute($context->getOperationName(), 'factory');
        if (null === $factoryConfig) {
            throw new \RuntimeException(sprintf('No resource factory found for class %s', $context->getClassName()));
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
