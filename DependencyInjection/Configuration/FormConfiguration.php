<?php

namespace Videni\Bundle\RestBundle\DependencyInjection\Configuration;

use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Oro\Component\EntitySerializer\ConfigUtil;

class FormConfiguration
{
    public function configure()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder
            ->root('forms')
            ->useAttributeAsKey('name')
            ->arrayPrototype()
                ->children()
                    ->scalarNode('form_type')->end()
                    ->arrayNode('form_options')
                        ->useAttributeAsKey('name')
                        ->performNoDeepMerging()
                        ->prototype('variable')->end()
                    ->end()
                    ->variableNode('fields')
                        ->validate()
                            ->always(function ($v) {
                                if (\is_string($v)) {
                                    return [$v];
                                }
                                if (\is_array($v)) {
                                    return $v;
                                }
                                throw new \InvalidArgumentException(
                                    'The value must be a string or an array.'
                                );
                            })
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;


        return $rootNode;
    }
}
