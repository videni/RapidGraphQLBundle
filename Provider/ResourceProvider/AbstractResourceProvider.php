<?php

declare(strict_types=1);

namespace Videni\Bundle\RapidGraphQLBundle\Provider\ResourceProvider;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Videni\Bundle\RapidGraphQLBundle\Factory\ParametersParserInterface;
use Videni\Bundle\RapidGraphQLBundle\Config\Resource\Service;
use Videni\Bundle\RapidGraphQLBundle\Context\ResourceContext;

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

    public function getResource(ResourceContext $context, callable $getter)
    {
        /** @var Service */
        $providerConfig = $context->getAction()->getResourceProvider();
        if (null === $providerConfig) {
            throw new \RuntimeException(sprintf(
                'No resource provider found for resource %s operation %s action %s',
                $context->getResource()->getShortName(),
                $context->getOperationName(),
                $context->getAction()
            ));
        }

        if (!$this->container->has($providerConfig->getId())) {
            throw new \RuntimeException(
                sprintf(
                    'Service %s is not existed in service container, please make sure it is defined.',
                    $providerConfig->getId()
                )
            );
        }

        $providerService = $this->container->get($providerConfig->getId());

        $method = $this->getMethod($providerService, $providerConfig);
        $arguments = $this->getArguments($getter, $providerConfig);

        return $providerConfig->getSpread() ? $providerService->$method(...array_values($arguments)) : $providerService->$method($arguments);
    }

    protected function getMethod($providerService, Service $providerConfig): string
    {
        return $providerConfig->getMethod();
    }

    protected function getArguments(callable $getter, Service $providerConfig): array
    {
        $arguments = $providerConfig->getArguments() ?? [];
        if (!is_array($arguments)) {
            $arguments = [$arguments];
        }

        return $this->parametersParser->parseRequestValues($arguments, $getter);
    }
}
