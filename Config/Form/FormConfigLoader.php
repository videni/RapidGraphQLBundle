<?php

namespace Videni\Bundle\RestBundle\Config\Form;

/**
 * The loader for paginator
 */
class FormConfigLoader
{
    /**
     * {@inheritdoc}
     */
    public function load(array $config)
    {
        $formConfig = new FormFieldConfig();

        $this->loadField($config, $formConfig);

        return $formConfig;
    }

    private function loadField(array $config, FormFieldConfig $formFieldConfig)
    {
        if (array_key_exists('exclude', $config)) {
            $formFieldConfig->setExclude($config['exclude']);
        }
        if (array_key_exists('description', $config)) {
            $formFieldConfig->setDescription($config['description']);
        }
        if (array_key_exists('property_path', $config)) {
            $formFieldConfig->setPropertyPath($config['property_path']);
        }
        if (array_key_exists('data_type', $config)) {
            $formFieldConfig->setDataType($config['data_type']);
        }
        if (array_key_exists('target_class', $config)) {
            $formFieldConfig->setTargetClass($config['target_class']);
        }
        if (array_key_exists('target_type', $config)) {
            $formFieldConfig->setTargetType($config['target_type']);
        }
        if (array_key_exists('collapse', $config)) {
            $formFieldConfig->setCollapse($config['collapse']);
        }
        if (array_key_exists('depends_on', $config)) {
            $formFieldConfig->setDependsOn($config['depends_on']);
        }
        if (array_key_exists('form_options', $config)) {
            $formFieldConfig->setFormOptions($config['form_options']);
        }
        if (array_key_exists('form_type', $config)) {
            $formFieldConfig->setFormType($config['form_type']);
        }
        if (array_key_exists('data_transformer', $config)) {
            $formFieldConfig->setDataTransformer($config['data_transformer']);
        }
        if (array_key_exists('exclusion_policy', $config)) {
            $formFieldConfig->setExclusionPolicy($config['exclusion_policy']);
        }
        if (array_key_exists('form_event_subscriber', $config)) {
            $formFieldConfig->setFormEventSubscriber($config['form_event_subscriber']);
        }
        if (array_key_exists('fields', $config)) {
            foreach ($config['fields'] as $fieldName => $fieldConfig) {
                $childFieldConfig = new FormFieldConfig();
                $this->loadField($fieldConfig, $childFieldConfig);

                $formFieldConfig->addField($fieldName, $childFieldConfig);
            }
        }
    }
}
