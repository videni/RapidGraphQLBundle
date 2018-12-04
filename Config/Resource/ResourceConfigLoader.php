<?php

namespace Videni\Bundle\RestBundle\Config\Resource;

use Videni\Bundle\RestBundle\Config\AbstractConfigLoader;
use Videni\Bundle\RestBundle\Config\Form\FormConfigLoader;
use Videni\Bundle\RestBundle\Config\Paginator\PaginatorConfigLoader;
use Videni\Bundle\RestBundle\Config\Form\FormConfig;

/**
 * The loader for resource
 */
class ResourceConfigLoader
{
    private $formConfigLoader;
    private $paginatorConfigLoader;

    public function __construct(FormConfigLoader $formConfigLoader, PaginatorConfigLoader $paginatorConfigLoader)
    {
        $this->formConfigLoader = $formConfigLoader;
        $this->paginatorConfigLoader = $paginatorConfigLoader;
    }

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
            $resourceConfig->setNormalizationContext(SerializationConfig::fromArray($config['normalization_context']));
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
        if (array_key_exists('forms', $config)) {
            $this->loadForms($resourceConfig, $config['forms']);
        }

        return $resourceConfig;
    }

    private function loadOperation(ResourceConfig $resourceConfig, array $config = [])
    {
        foreach ($config as $configName => $configValue) {
            $resourceConfig->addOperation($configName, OperationConfig::fromArray($configValue));
        }
    }

    private function loadForms(ResourceConfig $resourceConfig, array $config = [])
    {
        foreach ($config as $configName => $configValue) {
            $resourceConfig->addForm($configName, $this->formConfigLoader->load($configValue));
        }
    }

    private function loadPaginator(ResourceConfig $resourceConfig, array $config = [])
    {
        foreach ($config as $configName => $configValue) {
            $resourceConfig->addPaginator($configName, $this->paginatorConfigLoader->load($configValue));
        }
    }
}
