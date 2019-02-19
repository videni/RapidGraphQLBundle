<?php

namespace Videni\Bundle\RestBundle\Config\Resource;

use Videni\Bundle\RestBundle\Config\AbstractConfigLoader;
use Videni\Bundle\RestBundle\Config\Grid\GridLoader;

/**
 * The loader for resource
 */
class ResourceLoader
{
    private $paginatorConfigLoader;

    public function __construct(GridLoader $paginatorConfigLoader)
    {
        $this->paginatorConfigLoader = $paginatorConfigLoader;
    }

    /**
     * {@inheritdoc}
     */
    public function load(array $config)
    {
        $resourceConfig = new Resource();

        if (array_key_exists('denormalization_context', $config)) {
            $resourceConfig->setDenormalizationContext(Serialization::fromArray($config['denormalization_context']));
        }
        if (array_key_exists('normalization_context', $config)) {
            $resourceConfig->setNormalizationContext(Serialization::fromArray($config['normalization_context']));
        }
        if (array_key_exists('validation_groups', $config)) {
            $resourceConfig->setValidationGroups($config['validation_groups']);
        }
        if (array_key_exists('formats', $config)) {
            $resourceConfig->setFormats($config['formats']);
        }
        if (array_key_exists('factory_class', $config)) {
            $resourceConfig->setFactoryClass($config['factory_class']);
        }
        if (array_key_exists('repository_class', $config)) {
            $resourceConfig->setRepositoryClass($config['repository_class']);
        }
        if (array_key_exists('route_prefix', $config)) {
            $resourceConfig->setRoutePrefix($config['route_prefix']);
        }
        if (array_key_exists('short_name', $config)) {
            $resourceConfig->setShortName($config['short_name']);
        }
        if (array_key_exists('scope', $config)) {
            $resourceConfig->setScope($config['scope']);
        }
        if (array_key_exists('form', $config)) {
            $resourceConfig->setForm($config['form']);
        }
        if (array_key_exists('identifierFieldNames', $config)) {
            $formConfig->setDisableSorting($config['identifier_field_names']);
        }
        if (array_key_exists('parentResourceClass', $config)) {
            $formConfig->setDisableSorting($config['parent_resource_class']);
        }
        if (array_key_exists('operations', $config)) {
            $this->loadOperation($resourceConfig, $config['operations']);
        }
        if (array_key_exists('paginators', $config)) {
            $this->loadPaginator($resourceConfig, $config['paginators']);
        }

        return $resourceConfig;
    }

    private function loadOperation(Resource $resourceConfig, array $config = [])
    {
        foreach ($config as $configName => $configValue) {
            $resourceConfig->addOperation($configName, Operation::fromArray($configValue));
        }
    }

    private function loadPaginator(Resource $resourceConfig, array $config = [])
    {
        foreach ($config as $configName => $configValue) {
            $resourceConfig->addPaginator($configName, $this->paginatorConfigLoader->load($configValue));
        }
    }
}
