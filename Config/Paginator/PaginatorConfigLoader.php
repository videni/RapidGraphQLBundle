<?php

namespace Videni\Bundle\RestBundle\Config\Paginator;

use Videni\Bundle\RestBundle\Config\AbstractConfigLoader;

/**
 * The loader for paginator
 */
class PaginatorConfigLoader
{
    /**
     * {@inheritdoc}
     */
    public function load(array $config)
    {
        $paginatorConfig = new PaginatorConfig();

        if (array_key_exists('filters', $config)) {
            foreach ($config['filters'] as $filterName => $filterConfig) {
                $paginatorConfig->addFilter($filterName, FilterConfig::fromArray($filterConfig));
            }
        }
        if (array_key_exists('sortings', $config)) {
            foreach ($config['sortings'] as $sortingName => $sortingConfig) {
                $paginatorConfig->addSorting($sortingName, SortingConfig::fromArray($sortingConfig));
            }
        }
        if (array_key_exists('class', $config)) {
            $paginatorConfig->setClass($config['class']);
        }
        if (array_key_exists('max_results', $config)) {
            $paginatorConfig->setMaxResults($config['max_results']);
        }
        if (array_key_exists('disable_sorting', $config)) {
            $paginatorConfig->setDisableSorting($config['disable_sorting']);
        }

        foreach ($config['actions'] as $name => $actionGroupConfiguration) {
            $paginatorConfig->addActionGroup($this->convertActionGroup($name, $actionGroupConfiguration));
        }

        return $paginatorConfig;
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
}
