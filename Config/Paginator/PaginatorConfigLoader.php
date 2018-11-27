<?php

namespace App\Bundle\RestBundle\Config\Paginator;

use App\Bundle\RestBundle\Config\AbstractConfigLoader;

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
        if (array_key_exists('page_size', $config)) {
            $paginatorConfig->setPageSize($config['page_size']);
        }
        if (array_key_exists('disable_sorting', $config)) {
            $paginatorConfig->setDisableSorting($config['disable_sorting']);
        }

        return $paginatorConfig;
    }
}
