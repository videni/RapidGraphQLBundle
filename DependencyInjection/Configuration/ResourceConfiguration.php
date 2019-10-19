<?php

namespace Videni\Bundle\RestBundle\DependencyInjection\Configuration;

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Videni\Bundle\RestBundle\Doctrine\ORM\EntityRepository;
use Videni\Bundle\RestBundle\Factory\Factory;

class ResourceConfiguration implements ConfigurationInterface
{
    public const ROOT_NODE = "api";

    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root(self::ROOT_NODE);
        $children = $rootNode->children();

        $children
            ->append($this->addResourceConfigurationSection())
        ;

        return $treeBuilder;
    }

    private function addResourceConfigurationSection()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('resources');

        $rootNode
            ->beforeNormalization()
                ->always(function ($v) {
                    foreach ($v as $resourceName => &$value) {
                        if (!isset($value['short_name'])) {
                            $value['short_name'] = $resourceName;
                        }

                        if (isset($value['repository_class']) && !class_exists($value['repository_class'])) {
                            throw new \InvalidArgumentException(
                                sprintf('repository_class %s of resource %s is not found', $value['repository_class'], $resourceClass)
                            );
                        }

                        if (isset($value['factory_class']) && !class_exists($value['factory_class'])) {
                            throw new \InvalidArgumentException(
                                sprintf('factory_class %s of resource %s is not found', $value['factory_class'], $resourceClass)
                            );
                        }
                    }

                    return $v;
                })
            ->end()
            ->useAttributeAsKey('resource_name')
            ->arrayPrototype()
                ->children()
                    ->scalarNode('scope')->defaultValue('videni_rest')->cannotBeEmpty()->end()
                    ->scalarNode('short_name')->end()
                    ->arrayNode('form')
                        ->beforeNormalization()
                            ->ifString()
                            ->then(function ($v) {
                                return ['class' => $v];
                            })
                        ->end()
                        ->children()
                            ->scalarNode('class')->end()
                            ->arrayNode('validation_groups')
                               ->prototype('scalar')->end()
                            ->end()
                        ->end()
                    ->end()
                    ->scalarNode('repository_class')->defaultValue(EntityRepository::class)->cannotBeEmpty()->end()
                    ->scalarNode('factory_class')->defaultValue(Factory::class)->cannotBeEmpty()->end()
                    ->scalarNode('entity_class')
                       ->cannotBeEmpty()
                       ->isRequired()
                       ->validate()
                            ->ifTrue(function($v){
                                return !class_exists($v);
                            })
                            ->thenInvalid('Entity class %s is not existed')
                        ->end()
                    ->end()
                ->end()
            ->end()
        ->end();

        return $rootNode;
    }
}
