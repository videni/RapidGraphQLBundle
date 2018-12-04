<?php

namespace Videni\Bundle\RestBundle\DependencyInjection\Configuration;

use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Oro\Component\EntitySerializer\ConfigUtil;

class FieldConfiguration
{
    private $maxNestingLevel;

    public function __construct($maxNestingLevel = 0)
    {
        $this->maxNestingLevel = $maxNestingLevel;
    }

    public function configure()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder
            ->root('fields')
        ;

        $fieldsNode = $this->configureFormNode($rootNode);

        for (; $this->maxNestingLevel > 0; $this->maxNestingLevel--) {
            $fieldsNode = $fieldsNode
                ->arrayNode('fields');
            $fieldsNode = $this->configureFormNode($fieldsNode);
        }

        return $rootNode;
    }

    protected function configureFormNode(ArrayNodeDefinition $node)
    {
        $formNode = $node
            ->useAttributeAsKey('name')
            ->arrayPrototype()
                ->children()
        ;

        $this->configureFieldNode($formNode);

        $fieldsNode = $formNode
            ->arrayNode('fields')
                ->useAttributeAsKey('name')
                ->normalizeKeys(false)
                ->arrayPrototype()
                    ->children();

        $this->configureFieldNode($fieldsNode);

        return $fieldsNode;
    }

    protected function configureFieldNode(NodeBuilder $node): void
    {
        $node
            ->booleanNode('exclude')->end()
            ->scalarNode('description')->cannotBeEmpty()->end()
            ->scalarNode('property_path')->cannotBeEmpty()->end()
            ->scalarNode('data_type')->cannotBeEmpty()->end()
            ->scalarNode('description')->end()
            ->scalarNode('data_transformer')->end()
            ->scalarNode('target_class')->end()
            ->enumNode('target_type')
                ->values(['to-many', 'to-one', 'collection'])
            ->end()
            ->booleanNode('collapse')->end()
            ->scalarNode('form_type')->end()
            ->arrayNode('form_options')
                ->useAttributeAsKey('name')
                ->performNoDeepMerging()
                ->prototype('variable')->end()
            ->end()
            ->arrayNode('depends_on')
                ->prototype('scalar')->end()
            ->end()
            ->enumNode(ConfigUtil::EXCLUSION_POLICY)
                ->values([ConfigUtil::EXCLUSION_POLICY_ALL, ConfigUtil::EXCLUSION_POLICY_NONE])
            ->end()
            ->variableNode('form_event_subscribers')
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
        ;
    }
}
