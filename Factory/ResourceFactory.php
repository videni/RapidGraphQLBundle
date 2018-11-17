<?php

declare(strict_types=1);

namespace App\Bundle\RestBundle\Factory;

use Symfony\Component\DependencyInjection\ContainerInterface;

final class ResourceFactory implements ResourceFactoryInterface
{
    private $container;
    private $parameterParser;

    public function __construct(ContainerInterface $container, ParametersParserInterface $parameterParser)
    {
        $this->container = $container;
        $this->parameterParser = $parameterParser;
    }

    /**
     * {@inheritdoc}
     */
    public function create(Request $request, array $factoryConfigurations)
    {
        $factory = null;

        if (!isset($factoryConfigurations['class'])) {
            throw new \RuntimeException('No resource class found');
        }

        if (isset($factoryConfigurations['id'])) {
            $factory = $this->container->get($factoryConfigurations['id']);
        } else {
            $factory = new Factory($factoryConfigurations['class']);
        }
        $method = isset($factoryConfigurations['method'])? $factoryConfigurations['method']: 'createNew';

        $arguments = isset($factoryConfigurations['arguments'])? $factoryConfigurations['arguments']: [];
        if (!is_array($arguments)) {
            $arguments = [$arguments];
        }

        $arguments = array_values($this->parametersParser->parseRequestValues($arguments, $request));

        return $factory->$method(...$arguments);
    }
}
