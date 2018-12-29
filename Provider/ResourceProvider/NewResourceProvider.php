<?php

namespace Videni\Bundle\RestBundle\Provider\ResourceProvider;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Videni\Bundle\RestBundle\Factory\ParametersParserInterface;
use Symfony\Component\HttpFoundation\Request;
use Videni\Bundle\RestBundle\Config\Resource\ResourceConfig;
use Videni\Bundle\RestBundle\Config\Resource\ServiceConfig;
use Videni\Bundle\RestBundle\Context\ResourceContext;
use Videni\Bundle\RestBundle\Operation\ActionTypes;

class NewResourceProvider implements ResourceProviderInterface
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
    public function get(ResourceContext $context, Request $request)
    {
        $force = $request->attributes->get('_treat_as_new', false);
        if ($force) {
            return $this->create($context, $request);
        }

        if ($context->getAction() !== ActionTypes::CREATE) {
            return;
        }

        return $this->create($context, $request);
    }

    /**
     * {@inheritdoc}
     */
    public function create(ResourceContext $context, Request $request)
    {
        $resourceConfig = $context->getResourceConfig();

        /** @var ServiceConfig  */
        $factoryConfig = $resourceConfig->getOperationAttribute($context->getOperationName(), 'factory', true);
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
