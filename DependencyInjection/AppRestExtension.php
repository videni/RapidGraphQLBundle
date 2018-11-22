<?php

namespace App\Bundle\RestBundle\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use App\Bundle\RestBundle\Processor\ActionProcessorBag;
use Oro\Component\ChainProcessor\Debug\TraceLogger;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Oro\Component\ChainProcessor\Debug\TraceableActionProcessor;
use App\Bundle\RestBundle\Filter\FilterOperatorRegistry;
use App\Bundle\RestBundle\Filter\FilterValueAccessorFactory;
use Oro\Component\Config\Loader\YamlCumulativeFileLoader;
use Oro\Component\Config\Loader\CumulativeConfigLoader;

class AppRestExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yaml');

        $this->registerActionProcessors($container, $config);
        $this->registerFilterOperators($container, $config);

        $this->loadPaginatorConfiguration($container, $config);

        $container->setParameter('app_rest.confg', $config);
    }

     /**
     * @param ContainerBuilder $container
     * @param array            $config
     */
    private function registerActionProcessors(ContainerBuilder $container, array $config)
    {
        $actionProcessorBagServiceDef = $container->getDefinition(ActionProcessorBag::class);
        if (null !== $actionProcessorBagServiceDef) {
            $debug = $container->getParameter('kernel.debug');
            $logger = new Reference('logger', ContainerInterface::IGNORE_ON_INVALID_REFERENCE);
            foreach ($config['actions'] as $action => $actionConfig) {
                if (empty($actionConfig['processor_service_id'])) {
                    continue;
                }
                $actionProcessorServiceId = $actionConfig['processor_service_id'];
                // inject the logger for "api" channel into an action processor
                // we have to do it in this way rather than in service.yml to avoid
                // "The service definition "logger" does not exist." exception
                $container->getDefinition($actionProcessorServiceId)
                    ->addTag('monolog.logger', ['channel' => 'api'])
                    ->addMethodCall('setLogger', [$logger]);
                // register an action processor in the bag
                $actionProcessorBagServiceDef->addMethodCall(
                    'addProcessor',
                    [new Reference($actionProcessorServiceId)]
                );

                // decorate with TraceableActionProcessor
                if ($debug) {
                    $actionProcessorDecoratorServiceId = $actionProcessorServiceId . '.app_rest_api.profiler';
                    $container
                        ->register($actionProcessorDecoratorServiceId, TraceableActionProcessor::class)
                        ->setArguments([
                            new Reference($actionProcessorDecoratorServiceId . '.inner'),
                            new Reference(TraceLogger::class)
                        ])
                        // should be at the top of the decoration chain
                        ->setDecoratedService($actionProcessorServiceId, null, -255)
                        ->setPublic(false);
                }
            }
        }
    }

    /**
     * @param ContainerBuilder $container
     * @param array            $config
     */
    private function registerFilterOperators(ContainerBuilder $container, array $config)
    {
        $filterOperatorRegistryDef = $container->getDefinition(FilterOperatorRegistry::class);

        if (null !== $filterOperatorRegistryDef) {
            $filterOperatorRegistryDef->replaceArgument(0, $config['filter_operators']);
        }
        $restFilterValueAccessorFactoryDef = $container->getDefinition(FilterValueAccessorFactory::class);
        if (null !== $restFilterValueAccessorFactoryDef) {
            $restFilterValueAccessorFactoryDef->replaceArgument(1, $config['filter_operators']);
        }
    }

    /**
     * @param string $fileName
     *
     * @return array
     */
    private function loadPaginatorConfiguration($container): array
    {
        $configFileLoaders = [new YamlCumulativeFileLoader('Resources/config/app/api')];

        $config = [];
        $configLoader = new CumulativeConfigLoader('app_rest', $configFileLoaders);
        $resources = $configLoader->load($container);
        foreach ($resources as $resource) {
            if (array_key_exists(PaginatorConfiguration::ROOT_NODE, $resource->data)) {
                $config[] = $resource->data[PaginatorConfiguration::ROOT_NODE];
            }
        }

        return $this->processConfiguration(
            new PaginatorConfiguration($container->get(FilterOperatorRegistry::class)),
            $config
        );
    }
}
