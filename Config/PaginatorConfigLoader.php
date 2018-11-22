<?php

namespace App\Bundle\RestBundle\Config;

/**
 * The loader for paginator
 */
class PaginatorConfigLoader extends AbstractConfigLoader
{
    public const SORTINGS = 'sortings';
    public const FILTERS = 'filters';

    private const FIELD_METHOD_MAP = [
        FilterConfig::ALLOW_ARRAY => 'setArrayAllowed',
        FilterConfig::ALLOW_RANGE => 'setRangeAllowed'
    ];

    /**
     * {@inheritdoc}
     */
    public function load(array $config)
    {
        $paginatorConfig = new PaginatorConfig();
        foreach ($config as $key => $value) {
            if (self::SORTINGS === $key) {
                $this->loadSortings($paginatorConfig, $value);
            } else if (self::FILTERS === $key) {
                $this->loadFilters($paginatorConfig, $value);
            } else {
                $this->loadConfigValue($paginatorConfig, $key, $value);
            }
        }

        return $paginatorConfig;
    }

    protected function loadFilters(PaginatorConfig $paginatorConfig, array $config = null)
    {
        foreach ($config as $configName => $configValue) {
            $filterConfig =  new FilterConfig();

            if (!empty($configValue)) {
                foreach ($configValue as $name => $value) {
                    $this->loadConfigValue($filterConfig, $name, $value, self::FIELD_METHOD_MAP);
                }

                $paginatorConfig->addFilter($configName, $filter);
            }
        }
    }

    protected function loadSortings(PaginatorConfig $paginatorConfig, array $config = null)
    {
        foreach ($config as $configName => $configValue) {
            $sorting = new SortingConfig();
            if (!empty($configValue)) {
                foreach ($configValue as $key => $value) {
                    $this->loadConfigValue($sorting, $key, $value);
                }
                $paginatorConfig->addSorting($configName, $sorting);
            }
        }
    }
}
