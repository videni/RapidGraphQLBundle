<?php

namespace Videni\Bundle\RapidGraphQLBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Videni\Bundle\RapidGraphQLBundle\Normalizer\FormView\FormViewNormalizerResolver;

class RegisterFormViewNormalizerPass implements CompilerPassInterface
{
     /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        $chainDefinition = $container->getDefinition(FormViewNormalizerResolver::class);
        $taggedServiceIds = $container->findTaggedServiceIds('videni_rapid_graphql.form_view.normalizer');

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
