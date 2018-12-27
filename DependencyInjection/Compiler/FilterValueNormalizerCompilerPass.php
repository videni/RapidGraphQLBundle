<?php

namespace Videni\Bundle\RestBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Videni\Bundle\RestBundle\Filter\Normalizer\NormalizerCompositor;

class FilterValueNormalizerCompilerPass implements CompilerPassInterface
{
    const FILTER_VALUE_NORMALIZER_TAG = 'videni_rest.filter_value.normalizer';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->has(NormalizerCompositor::class)) {
            return;
        }

        $normalizerCompositorDef = $container->findDefinition(NormalizerCompositor::class);

        $taggedServices = $container->findTaggedServiceIds(self::FILTER_VALUE_NORMALIZER_TAG);

        foreach($taggedServices as $id => $attribtues) {
            if(isset($attribtues[0]['dataType'])) {
                $dataTypes = explode('|', $attribtues[0]['dataType']);
                foreach($dataTypes as $dataType) {
                    $normalizerCompositorDef->addMethodCall('add', [$dataType, new Reference($id)]);
                }
            }
        }
    }
}
