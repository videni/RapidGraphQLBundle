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
        $formConfig = new FormConfig();

        if (array_key_exists('fields', $config)) {
            foreach ($config['fields'] as $fieldName => $fieldConfig) {
                $formConfig->addField($fieldName, FieldConfig::fromArray($fieldConfig));
            }
        }
        if (array_key_exists('exclusionPolicy', $config)) {
            $formConfig->setMaxResults($config['exclusion_policy']);
        }
        if (array_key_exists('postSerialize', $config)) {
            $formConfig->setDisableSorting($config['post_serialize']);
        }
        if (array_key_exists('formType', $config)) {
            $formConfig->setDisableSorting($config['form_type']);
        }
        if (array_key_exists('formOptions', $config)) {
            $formConfig->setDisableSorting($config['form_options']);
        }
        if (array_key_exists('formEventSubscriber', $config)) {
            $formConfig->setDisableSorting($config['form_event_subscriber']);
        }
        if (array_key_exists('parentResourceClass', $config)) {
            $formConfig->setDisableSorting($config['parent_resource_class']);
        }
        if (array_key_exists('identifierFieldNames', $config)) {
            $formConfig->setDisableSorting($config['identifier_field_names']);
        }

        return $formConfig;
    }
}
