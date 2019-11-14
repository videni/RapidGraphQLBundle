<?php

namespace Videni\Bundle\RapidGraphQLBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Doctrine\Common\Inflector\Inflector;
use Videni\Bundle\RapidGraphQLBundle\Operation\ActionTypes;
use Videni\Bundle\RapidGraphQLBundle\Form\Handler\FormHandlerInterface;
use Videni\Bundle\RapidGraphQLBundle\Form\Handler\FormHandler;

class Configuration implements ConfigurationInterface
{
    public const NAME = 'videni_rapid_graphql';

    public const RESOURCE_PROVIDER_KEY = 'resource_provider';

    private $resourceConfigs;

    public function __construct(array $resourceConfigs)
    {
        $this->resourceConfigs = $resourceConfigs;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root(self::NAME);

        $node = $rootNode
            ->children()
        ;

        $node->append($this->addOpertaionConfigurationSection());

        return $treeBuilder;
    }

    public function addOpertaionConfigurationSection()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('operations');

        $resourceConfigs = $this->resourceConfigs;
        $resourceNames = array_keys($resourceConfigs);

        $rootNode
             ->beforeNormalization()
                ->always(function ($v) use($resourceConfigs) {
                    foreach ($v as $operationName => &$value) {
                        if (!isset($value['resource'])) {
                            throw new \LogicException(sprintf('The resource key is missing for operation %s', $operationName));
                        }

                        if (!isset($resourceConfigs[$value['resource']])) {
                            throw new \LogicException(sprintf('Resource %s is not registered', $value['resource']));
                        }

                        $resourceConfig = $resourceConfigs[$value['resource']];
                        $this->normalizeActions($resourceConfig['scope'], $value['resource'], $operationName, $value);
                    }

                    return $v;
                })
            ->end()
            ->useAttributeAsKey('operationName')
            ->arrayPrototype()
                ->children()
                    ->arrayNode('validation_groups')
                            ->prototype('scalar')->end()
                    ->end()
                    ->scalarNode('resource')
                        ->isRequired()
                        ->cannotBeEmpty()
                        ->validate()
                            ->ifTrue(function($v) use ($resourceNames){
                                return !in_array($v, $resourceNames);
                            })
                            ->thenInvalid('Resource %s is not defined')
                        ->end()
                    ->end()
                    ->arrayNode('actions')
                        ->useAttributeAsKey('name')
                        ->cannotBeEmpty()
                        ->arrayPrototype()
                            ->beforeNormalization()
                                ->ifString()
                                ->then(function ($v) {
                                    return ['action' => $v];
                                })
                            ->end()
                            ->children()
                                ->scalarNode('grid')->end()
                                ->scalarNode('controller')->end()
                                ->scalarNode('access_control')->end()
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
                                        ->scalarNode('handler')
                                            ->cannotBeEmpty()
                                            ->defaultValue(FormHandler::class)
                                        ->end()
                                    ->end()
                                ->end()
                                ->scalarNode('access_control_message')->end()
                                ->scalarNode('action')->end()
                                ->arrayNode(self::RESOURCE_PROVIDER_KEY)
                                        ->beforeNormalization()
                                            ->ifString()
                                            ->then(function ($v) {
                                                return ['id' => $v];
                                            })
                                        ->end()
                                        ->children()
                                            ->scalarNode('id')->end()
                                            ->scalarNode('method')->end()
                                            ->scalarNode('spread')->defaultValue(true)->end()
                                            ->arrayNode('arguments')
                                                ->prototype('scalar')->end()
                                            ->end()
                                        ->end()
                                ->end()
                                ->arrayNode('validation_groups')
                                    ->prototype('scalar')->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $rootNode;
    }


    private function normalizeActions($scope, $resourceName, $operationName,  &$value)
    {
        if(!array_key_exists('actions', $value)) {
            return;
        }

        foreach($value['actions'] as $actionName => &$actionConfig) {
            $this->setDefaultAction($actionConfig, $actionName, $operationName);
            if (ActionTypes::INDEX === $actionConfig['action'] && !isset($actionConfig['grid'])) {
                throw new \LogicException(
                    sprintf(
                        'Grid is missing for operation %s %s action, index action must have a grid defined.',
                        $operationName,
                        $actionName
                    )
                );
            }
            if (ActionTypes::CREATE === $actionConfig['action']) {
                $this->setDefaultResourceProviderConfig($this->getServiceId($scope, $resourceName, 'factory'), $actionConfig);
            } else {
                $this->setDefaultResourceProviderConfig($this->getServiceId($scope, $resourceName, 'repository'), $actionConfig);
            }

            if (in_array($actionConfig['action'], [ActionTypes::CREATE, ActionTypes::UPDATE])) {
                if(!isset($actionConfig['form'])) {
                    $actionConfig['form'] =  isset($this->resourceConfigs[$resourceName]['form'])? $this->resourceConfigs[$resourceName]['form']: null;
                } else {
                    $toArray = function($formConfig) {
                        return is_array($formConfig)? $formConfig: ['class' => $formConfig];
                    };

                    $actionConfig['form'] = array_replace_recursive($toArray($this->resourceConfigs[$resourceName]['form']), $toArray($actionConfig['form']));
                }
            }
        }
    }

    private function setDefaultAction(&$actionConfig, $actionName, $operationName)
    {
        $defaultActions = [
            ActionTypes::UPDATE,
            ActionTypes::INDEX,
            ActionTypes::CREATE,
            ActionTypes::VIEW,
            ActionTypes::DELETE,
            ActionTypes::BULK_DELETE,
        ];

        if (!isset($actionConfig['action'])) {
            if (!in_array($actionName, $defaultActions)) {
                throw new \LogicException(
                    sprintf(
                        'There is no action type defined for operation %s of action %s, none default operation must have action type defined',
                        $operationName,
                        $actionName
                    )
                );
            }
            $append = [
                'action' => $actionName,
            ];

            $actionConfig = is_null($actionConfig) ? $append:  array_merge($actionConfig, $append);
        } else if(!in_array($actionConfig['action'], $defaultActions)) {
            throw new \LogicException(
                sprintf(
                    'Action type %s of operation %s of action %s is not existed, only %s are supported', $actionConfig['action'],
                    $operationName,
                    $actionName,
                    implode(',', $defaultActions)
                )
            );
        }
    }

    private function setDefaultResourceProviderConfig($serviceId, &$actionConfig)
    {
        $config = [
            "id" => $serviceId,
        ];

        $key = self::RESOURCE_PROVIDER_KEY;

        $actionConfig[$key] = isset($actionConfig[$key]) ?
            array_merge(
                $config,
                is_array($actionConfig[$key]) ? $actionConfig[$key]: ['id' => $actionConfig[$key]]
            ) : $config
        ;
    }

    private function getServiceId($scope, $resourceName, $key)
    {
         $name = Inflector::tableize($resourceName);

         return sprintf('%s.%s.%s', $scope, $key, $name);
    }
}
