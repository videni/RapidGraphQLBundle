<?php

namespace Videni\Bundle\RestBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Videni\Bundle\RestBundle\Normalizer\FormView\FormViewNormalizerResolver;

class RegisterFormViewNormalizerPass implements CompilerPassInterface
{
     /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        $chainDefinition = $container->getDefinition(FormViewNormalizerResolver::class);
        $taggedServiceIds = $container->findTaggedServiceIds('videni_rest.form_view.normalizer');

        foreach ($taggedServiceIds as $serviceId => $tags) {
            $chainDefinition->addMethodCall(
                'addNormalizer',
                [
                    new Reference($serviceId),
                    isset($tags[0]['priority'])? $tags[0]['priority']: 0
                ]
            );
        }
    }
}
