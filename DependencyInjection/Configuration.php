<?php

namespace Videni\Bundle\RestBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Videni\Bundle\RestBundle\Decoder\JsonDecoder;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\Common\Inflector\Inflector;
use Videni\Bundle\RestBundle\Operation\ActionTypes;

class Configuration implements ConfigurationInterface
{
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
        $rootNode = $treeBuilder->root('videni_rest');

        $node = $rootNode
            ->children()
                ->scalarNode('api_version')->defaultValue(1)->end()
        ;

        $this->addBodyListenerSection($node);
        $this->addExceptionToStatusSection($node);
        $node->append($this->addOpertaionConfigurationSection());

        return $treeBuilder;
    }

    private function addBodyListenerSection(NodeBuilder $node)
    {
        $decodersDefaultValue = ['json' => JsonDecoder::class];

        $node
            ->arrayNode('body_listener')
                ->fixXmlConfig('decoder', 'decoders')
                ->addDefaultsIfNotSet()
                ->canBeUnset()
                ->canBeDisabled()
                ->children()
                    ->scalarNode('service')->defaultNull()->end()
                    ->scalarNode('default_format')->defaultNull()->end()
                    ->booleanNode('throw_exception_on_unsupported_content_type')
                        ->defaultFalse()
                    ->end()
                    ->arrayNode('decoders')
                        ->useAttributeAsKey('name')
                        ->defaultValue($decodersDefaultValue)
                        ->prototype('scalar')->end()
                    ->end()
                    ->arrayNode('array_normalizer')
                        ->addDefaultsIfNotSet()
                        ->beforeNormalization()
                            ->ifString()->then(function ($v) {
                                return ['service' => $v];
                            })
                        ->end()
                        ->children()
                            ->scalarNode('service')->defaultNull()->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

     /**
     * Adds an exception to status section.
     *
     * @throws InvalidConfigurationException
     */
    private function addExceptionToStatusSection(NodeBuilder $node)
    {
        $node
            ->arrayNode('exception_to_status')
                ->defaultValue([
                    ExceptionInterface::class => Response::HTTP_BAD_REQUEST,
                    InvalidArgumentException::class => Response::HTTP_BAD_REQUEST,
                    FilterValidationException::class => Response::HTTP_BAD_REQUEST,
                    OptimisticLockException::class => Response::HTTP_CONFLICT,
                ])
                ->info('The list of exceptions mapped to their HTTP status code.')
                ->normalizeKeys(false)
                ->useAttributeAsKey('exception_class')
                ->beforeNormalization()
                    ->ifArray()
                    ->then(function (array $exceptionToStatus) {
                        foreach ($exceptionToStatus as &$httpStatusCode) {
                            if (\is_int($httpStatusCode)) {
                                continue;
                            }

                            if (\defined($httpStatusCodeConstant = sprintf('%s::%s', Response::class, $httpStatusCode))) {
                                @trigger_error(sprintf('Using a string "%s" as a constant of the "%s" class is deprecated since API Platform 2.1 and will not be possible anymore in API Platform 3. Use the Symfony\'s custom YAML extension for PHP constants instead (i.e. "!php/const %s").', $httpStatusCode, Response::class, $httpStatusCodeConstant), E_USER_DEPRECATED);

                                $httpStatusCode = \constant($httpStatusCodeConstant);
                            }
                        }

                        return $exceptionToStatus;
                    })
                ->end()
                ->prototype('integer')->end()
                ->validate()
                    ->ifArray()
                    ->then(function (array $exceptionToStatus) {
                        foreach ($exceptionToStatus as $httpStatusCode) {
                            if ($httpStatusCode < 100 || $httpStatusCode >= 600) {
                                throw new InvalidConfigurationException(sprintf('The HTTP status code "%s" is not valid.', $httpStatusCode));
                            }
                        }

                        return $exceptionToStatus;
                    })
                ->end()
            ->end()
        ;
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
                    ->scalarNode('route_prefix')->end()
                    ->arrayNode('validation_groups')
                            ->prototype('scalar')->end()
                    ->end()
                    ->arrayNode('normalization_context')
                        ->children()
                            ->arrayNode('groups')
                                ->prototype('variable')->end()
                            ->end()
                            ->scalarNode('enable_max_depth')->end()
                            ->scalarNode('section')->end()
                        ->end()
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
                                ->scalarNode('path')->end()
                                ->scalarNode('grid')->end()
                                ->scalarNode('route_name')->end()
                                ->scalarNode('controller')->end()
                                ->scalarNode('access_control')->end()
                                ->scalarNode('form')->end()
                                ->scalarNode('access_control_message')->end()
                                ->scalarNode('action')->end()
                                ->arrayNode('methods')
                                    ->prototype('scalar')->end()
                                ->end()
                                ->arrayNode('defaults')
                                    ->performNoDeepMerging()
                                    ->variablePrototype()->end()
                                ->end()
                                ->arrayNode('requirements')
                                    ->performNoDeepMerging()
                                    ->variablePrototype()->end()
                                ->end()
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
                                            ->scalarNode('spread_arguments')->defaultValue(true)->end()
                                            ->arrayNode('arguments')
                                                ->prototype('scalar')->end()
                                            ->end()
                                        ->end()
                                ->end()
                                ->arrayNode('validation_groups')
                                    ->prototype('scalar')->end()
                                ->end()
                                ->arrayNode('normalization_context')
                                    ->children()
                                        ->arrayNode('groups')
                                            ->prototype('variable')->end()
                                        ->end()
                                        ->scalarNode('enable_max_depth')->end()
                                        ->scalarNode('section')->end()
                                        ->scalarNode('version')->end()
                                    ->end()
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
                    $actionConfig['form'] =  isset($this->resourceConfigs[$resourceName]['form'])? $this->resourceConfigs[$resourceName]['form']['class']: null;
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
