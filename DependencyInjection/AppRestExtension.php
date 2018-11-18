<?php

namespace App\Bundle\RestBundle\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use App\Bundle\RestBundle\Processor\ActionProcessorBag;
use Oro\Component\ChainProcessor\Debug\TraceLogger;

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
                    $actionProcessorDecoratorServiceId = $actionProcessorServiceId . '.simple_rest_api.profiler';
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
}
