<?php

namespace Videni\Bundle\RestBundle\DependencyInjection\Compiler;

use Videni\Bundle\RestBundle\Util\DependencyInjectionUtil;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Videni\Bundle\RestBundle\Filter\Factory\ChainFilterFactory;
use Videni\Bundle\RestBundle\Filter\Factory\SimpleFilterFactory;

/**
 * Configures factories responsible to create instances of filters that can be used in Data API.
 */
class FilterFactoryCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $filterFactoryServiceDef = DependencyInjectionUtil::findDefinition(
            $container,
            SimpleFilterFactory::class
        );
        if (null !== $filterFactoryServiceDef) {
            $config = DependencyInjectionUtil::getConfig($container);
            foreach ($config['filters'] as $filterType => $parameters) {
                if (isset($parameters['factory'])) {
                    $factory = $parameters['factory'];
                    unset($parameters['factory']);
                    $filterFactoryServiceDef->addMethodCall(
                        'addFilterFactory',
                        [$filterType, new Reference(substr($factory[0], 1)), $factory[1], $parameters]
                    );
                } else {
                    $filterClassName = $parameters['class'];
                    unset($parameters['class']);
                    $filterFactoryServiceDef->addMethodCall(
                        'addFilter',
                        [$filterType, $filterClassName, $parameters]
                    );
                }
            }
        }

        DependencyInjectionUtil::registerTaggedServices(
            $container,
            ChainFilterFactory::class,
            'videni_rest.filter_factory',
            'addFilterFactory'
        );
    }
}
