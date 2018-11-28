<?php

namespace Videni\Bundle\RestBundle\Config\Resource;

use Videni\Bundle\RestBundle\Config\AbstractConfigLoader;

/**
 * The loader for resource
 */
class ResourceConfigLoader
{
    /**
     * {@inheritdoc}
     */
    public function load(array $config)
    {
        $resourceConfig = new ResourceConfig();

        if (array_key_exists('denormalization_context', $config)) {
            $resourceConfig->setDenormalizationContext(SerializationConfig::fromArray($config['denormalization_context']));
        }
        if (array_key_exists('normalization_context', $config)) {
            $resourceConfig->setDenormalizationContext(SerializationConfig::fromArray($config['normalization_context']));
        }
        if (array_key_exists('validation_groups', $config)) {
            $resourceConfig->setValidationGroups($config['validation_groups']);
        }
        if (array_key_exists('formats', $config)) {
            $resourceConfig->setFormats($config['formats']);
        }
        if (array_key_exists('factory', $config)) {
            $resourceConfig->setFactory(ServiceConfig::fromArray($config['factory']));
        }
        if (array_key_exists('repository', $config)) {
            $resourceConfig->setRepository(ServiceConfig::fromArray($config['repository']));
        }

        if (array_key_exists('route_prefix', $config)) {
            $resourceConfig->setRoutePrefix($config['route_prefix']);
        }
        if (array_key_exists('short_name', $config)) {
            $resourceConfig->setShortName($config['short_name']);
        }
        if (array_key_exists('operations', $config)) {
            $this->loadOperation($resourceConfig, $config['operations']);
        }

        return $resourceConfig;
    }

    private function loadOperation(ResourceConfig $resourceConfig, array $config = null)
    {
        foreach ($config as $configName => $configValue) {
            $resourceConfig->addOperation($configName, OperationConfig::fromArray($configValue));
        }
    }
}
