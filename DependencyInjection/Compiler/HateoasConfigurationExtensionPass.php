<?php

namespace Videni\Bundle\RestBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Alias;
use Hateoas\Configuration\Metadata\Driver\ExtensionDriver;

/**
 *  Enable ExtensionDeriver for all Hateoas metadata drivers(yaml, xml, annotation).
 *  remove this when https://github.com/willdurand/BazingaHateoasBundle/issues/85 is resolved.
 */
class HateoasConfigurationExtensionPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $container->getDefinition('hateoas.configuration.metadata.chain_driver')
        ->replaceArgument(0, [
            new Reference('hateoas.configuration.metadata.yaml_driver'),
            new Reference('hateoas.configuration.metadata.xml_driver'),
            new Reference('hateoas.configuration.metadata.annotation_driver'),
        ]);

        $container->getDefinition('hateoas.configuration.metadata.extension_driver')
            ->setDecoratedService('hateoas.configuration.metadata.chain_driver', 'hateoas.configuration.metadata.chain_driver.inner')
            ->replaceArgument(0, new Reference('hateoas.configuration.metadata.chain_driver.inner'))
        ;
    }
}
