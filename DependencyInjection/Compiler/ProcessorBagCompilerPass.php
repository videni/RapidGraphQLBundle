<?php

namespace Videni\Bundle\RestBundle\DependencyInjection\Compiler;

use Oro\Component\ChainProcessor\DependencyInjection\ProcessorsLoader;
use Oro\Component\ChainProcessor\ProcessorBagConfigBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Oro\Component\ChainProcessor\ProcessorBagConfigProvider;
use Videni\Bundle\RestBundle\Util\DependencyInjectionUtil;

/**
 * Adds all registered Data API processors to the processor bag service.
 */
class ProcessorBagCompilerPass implements CompilerPassInterface
{
    private const PROCESSOR_TAG  = 'videni_rest.processor';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $processorBagConfigProviderServiceDef = $container->getDefinition(ProcessorBagConfigProvider::class);

        if (null !== $processorBagConfigProviderServiceDef) {
            $groups = [];
            $config = DependencyInjectionUtil::getConfig($container);
            foreach ($config['actions'] as $action => $actionConfig) {
                if (isset($actionConfig['processing_groups'])) {
                    foreach ($actionConfig['processing_groups'] as $group => $groupConfig) {
                        $groups[$action][$group] = $this->getAttribute($groupConfig, 'priority', 0);
                    }
                }
            }
            $processors = ProcessorsLoader::loadProcessors($container, self::PROCESSOR_TAG);
            $builder = new ProcessorBagConfigBuilder($groups, $processors);
            $processorBagConfigProviderServiceDef->replaceArgument(0, $builder->getGroups());
            $processorBagConfigProviderServiceDef->replaceArgument(1, $builder->getProcessors());
        }
    }

        /**
     * Gets a value of the specific tag attribute.
     *
     * @param array  $attributes
     * @param string $attributeName
     * @param mixed  $defaultValue
     *
     * @return mixed
     */
    public static function getAttribute(array $attributes, $attributeName, $defaultValue)
    {
        if (!array_key_exists($attributeName, $attributes)) {
            return $defaultValue;
        }

        return $attributes[$attributeName];
    }
}
