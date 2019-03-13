<?php

declare(strict_types=1);

namespace Videni\Bundle\RestBundle\Provider\ResourceProvider;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Videni\Bundle\RestBundle\Factory\ParametersParserInterface;
use Videni\Bundle\RestBundle\Config\Resource\Resource;
use Videni\Bundle\RestBundle\Config\Resource\Service;
use Videni\Bundle\RestBundle\Context\ResourceContext;
use Videni\Bundle\RestBundle\Operation\ActionTypes;
use Videni\Bundle\RestBundle\Factory\FactoryInterface;

abstract class AbstractResourceProvider implements ResourceProviderInterface
{
    protected $container;
    protected $parametersParser;

    public function __construct(
        ContainerInterface $container,
        ParametersParserInterface $parametersParser
    ) {
        $this->container = $container;
        $this->parametersParser = $parametersParser;
    }

    public function getResource(ResourceContext $context, Request $request)
    {
        /** @var Service */
        $providerConfig = $context->getOperationConfig()->getResourceProvider();
        if (null === $providerConfig) {
            throw new \RuntimeException(sprintf('No resource provider found for class %s', $context->getClassName()));
        }

        $providerInstance = $this->container->get($providerConfig->getId());

        $method = $this->getMethod($providerConfig);
        $arguments = $this->getArguments($request, $providerConfig);

        return $providerConfig->getSpreadArguments() ? $providerInstance->$method(...array_values($arguments)) : $providerInstance->$method($arguments);
    }

    protected function getMethod(Service $providerConfig): string
    {
        return $providerConfig->getMethod();
    }

    protected function getArguments(Request $request, Service $providerConfig): array
    {
        $arguments = $providerConfig->getArguments() ?? [];
        if (!is_array($arguments)) {
            $arguments = [$arguments];
        }

        return array_values($this->parametersParser->parseRequestValues($arguments, $request));
    }
}
