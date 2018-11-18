<?php

namespace App\Bundle\RestBundle\DependencyInjection\Compiler;

use Oro\Component\ChainProcessor\DependencyInjection\ProcessorsLoader;
use Oro\Component\ChainProcessor\ProcessorBagConfigBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use App\Component\ChainProcessor\ProcessorBagConfigProvider;

/**
 * Adds all registered Data API processors to the processor bag service.
 */
class ProcessorBagCompilerPass implements CompilerPassInterface
{
    private const PROCESSOR_TAG                            = 'simple_rest.api.processor';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $processorBagConfigProviderServiceDef = $containter->getDefinition(ProcessorBagConfigProvider::class);

        if (null !== $processorBagConfigProviderServiceDef) {
            $groups = [];
            $config = DependencyInjectionUtil::getConfig($container);
            foreach ($config['actions'] as $action => $actionConfig) {
                if (isset($actionConfig['processing_groups'])) {
                    foreach ($actionConfig['processing_groups'] as $group => $groupConfig) {
                        $groups[$action][$group] = DependencyInjectionUtil::getPriority($groupConfig);
                    }
                }
            }
            $processors = ProcessorsLoader::loadProcessors($container, self::PROCESSOR_TAG);
            $builder = new ProcessorBagConfigBuilder($groups, $processors);
            $processorBagConfigProviderServiceDef->replaceArgument(0, $builder->getGroups());
            $processorBagConfigProviderServiceDef->replaceArgument(1, $builder->getProcessors());
        }
    }
}
