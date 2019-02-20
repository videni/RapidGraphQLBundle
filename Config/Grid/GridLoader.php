<?php

namespace Videni\Bundle\RestBundle\Config\Grid;

use Videni\Bundle\RestBundle\Config\AbstractConfigLoader;

/**
 * The loader for paginator
 */
class GridLoader
{
    /**
     * {@inheritdoc}
     */
    public function load(array $config)
    {
        $grid = new Grid();

        if (array_key_exists('filters', $config)) {
            foreach ($config['filters'] as $filterName => $filterConfig) {
                $grid->addFilter($filterName, Filter::fromArray($filterConfig));
            }
        }
        foreach ($config['fields'] as $name => $fieldConfiguration) {
            $grid->addField($this->convertField($name, $fieldConfiguration));
        }
        if (array_key_exists('class', $config)) {
            $grid->setClass($config['class']);
        }
        if (array_key_exists('max_results', $config)) {
            $grid->setMaxResults($config['max_results']);
        }
        if (array_key_exists('disable_sorting', $config)) {
            $grid->setDisableSorting($config['disable_sorting']);
        }

        foreach ($config['actions'] as $name => $actionGroupConfiguration) {
            $grid->addActionGroup($this->convertActionGroup($name, $actionGroupConfiguration));
        }

        return $grid;
    }

    /**
     * @param string $name
     * @param array $configuration
     *
     * @return ActionGroup
     */
    private function convertActionGroup(string $name, array $configuration): ActionGroup
    {
        $actionGroup = ActionGroup::named($name);

        foreach ($configuration as $actionName => $actionConfiguration) {
            $actionGroup->addAction($this->convertAction($actionName, $actionConfiguration));
        }

        return $actionGroup;
    }

     /**
     * @param string $name
     * @param array $configuration
     *
     * @return Action
     */
    private function convertAction(string $name, array $configuration): Action
    {
        $action = Action::fromNameAndType($name, $configuration['type']);

        if (array_key_exists('label', $configuration)) {
            $action->setLabel($configuration['label']);
        }
        if (array_key_exists('icon', $configuration)) {
            $action->setIcon($configuration['icon']);
        }
        if (array_key_exists('enabled', $configuration)) {
            $action->setEnabled($configuration['enabled']);
        }
        if (array_key_exists('position', $configuration)) {
            $action->setPosition($configuration['position']);
        }
        if (array_key_exists('options', $configuration)) {
            $action->setOptions($configuration['options']);
        }

        return $action;
    }

    /**
     * @param string $name
     * @param array $configuration
     *
     * @return Field
     */
    private function convertField(string $name, array $configuration): Field
    {
        $field = Field::fromNameAndType($name, $configuration['type']);

        if (array_key_exists('property_path', $configuration)) {
            $field->setPath($configuration['path']);
        }
        if (array_key_exists('label', $configuration)) {
            $field->setLabel($configuration['label']);
        }
        if (array_key_exists('enabled', $configuration)) {
            $field->setEnabled($configuration['enabled']);
        }
        if (array_key_exists('sorting', $configuration)) {
            $field->setSorting($configuration['sorting']);
        }
        if (array_key_exists('position', $configuration)) {
            $field->setPosition($configuration['position']);
        }
        if (array_key_exists('options', $configuration)) {
            $field->setOptions($configuration['options']);
        }

        return $field;
    }
}
