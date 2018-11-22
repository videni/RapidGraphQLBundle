<?php

namespace App\Bundle\RestBundle\Config;

/**
 * Represents the configuration of  paginator
 */
class PaginatorConfig implements ConfigBagInterface
{
    public const FILTERS = 'filters';
    public const MAX_RESULTS = 'max_results';
    public const PAGE_SIZE = 'page_size';
    public const DISABLE_SORTING = 'disable_sorting';

    /** @var array */
    protected $items = [];

    /** @var FilterConfig[] */
    protected $filters = [];

    protected $sortings =  [];

    /**
     * Gets a native PHP array representation of the configuration.
     *
     * @return array
     */
    public function toArray()
    {
        $result = ConfigHelper::convertItemsToArray($this->items);

        $filters = ConfigHelper::convertObjectsToArray($this->filters, true);
        if ($filters) {
            $result[self::FILTERS] = $filters;
        }

        $sortings = ConfigHelper::convertObjectsToArray($this->sortings, true);
        if ($sortings) {
            $result[self::SORTINGS] = $sortings;
        }

        return $result;
    }

    /**
     * Indicates whether the entity does not have a configuration.
     *
     * @return bool
     */
    public function isEmpty()
    {
        return empty($this->items)
            && empty($this->filters)
            && empty($this->sortings)
            ;
    }

    /**
     * Makes a deep copy of the object.
     */
    public function __clone()
    {
        $this->items = ConfigHelper::cloneItems($this->items);
        $this->filters = ConfigHelper::cloneObjects($this->filters);
    }

    /**
     * Checks whether the configuration of at least one filter exists.
     *
     * @return bool
     */
    public function hasFilters()
    {
        return !empty($this->filters);
    }

    /**
     * Gets the configuration for all filters.
     *
     * @return FilterConfig[] [filter name => config, ...]
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * Checks whether the configuration of the filter exists.
     *
     * @param string $filterName
     *
     * @return bool
     */
    public function hasFilter($filterName)
    {
        return isset($this->filters[$filterName]);
    }

    /**
     * Gets the configuration of the filter.
     *
     * @param string $filterName
     *
     * @return FilterConfig|null
     */
    public function getFilter($filterName)
    {
        if (!isset($this->filters[$filterName])) {
            return null;
        }

        return $this->filters[$filterName];
    }

    /**
     * Adds the configuration of the filter.
     *
     * @param string                 $filterName
     * @param FilterConfig|null $filter
     *
     * @return FilterConfig
     */
    public function addFilter($filterName, $filter = null)
    {
        if (null === $filter) {
            $filter = new FilterConfig();
        }

        $this->filters[$filterName] = $filter;

        return $filter;
    }

    /**
     * Removes the configuration of the filter.
     *
     * @param string $filterName
     */
    public function removeFilter($filterName)
    {
        unset($this->filters[$filterName]);
    }

    /**
     * Checks whether the configuration of at least one filter exists.
     *
     * @return bool
     */
    public function hasSortings()
    {
        return !empty($this->sortings);
    }

    /**
     * Gets the configuration for all Sortings.
     *
     * @return SortingConfig[] [Sorting name => config, ...]
     */
    public function getSortings()
    {
        return $this->sortings;
    }

    /**
     * Checks whether the configuration of the Sorting exists.
     *
     * @param string $sortingName
     *
     * @return bool
     */
    public function hasSorting($sortingName)
    {
        return isset($this->sortings[$sortingName]);
    }

    /**
     * Gets the configuration of the Sorting.
     *
     * @param string $sortingName
     *
     * @return SortingConfig|null
     */
    public function getSorting($sortingName)
    {
        if (!isset($this->sortings[$sortingName])) {
            return null;
        }

        return $this->sortings[$sortingName];
    }

    /**
     * Adds the configuration of the Sorting.
     *
     * @param string                 $sortingName
     * @param SortingConfig|null $sorting
     *
     * @return SortingConfig
     */
    public function addSorting($sortingName, $sorting = null)
    {
        if (null === $sorting) {
            $sorting = new SortingConfig();
        }

        $this->sortings[$sortingName] = $sorting;

        return $sorting;
    }

    /**
     * Removes the configuration of the Sorting.
     *
     * @param string $sortingName
     */
    public function removeSorting($sortingName)
    {
        unset($this->sortings[$sortingName]);
    }

    /**
     * {@inheritdoc}
     */
    public function has($key)
    {
        return \array_key_exists($key, $this->items);
    }

    /**
     * {@inheritdoc}
     */
    public function get($key, $defaultValue = null)
    {
        if (!\array_key_exists($key, $this->items)) {
            return $defaultValue;
        }

        return $this->items[$key];
    }

    /**
     * {@inheritdoc}
     */
    public function set($key, $value)
    {
        if (null !== $value) {
            $this->items[$key] = $value;
        } else {
            unset($this->items[$key]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function remove($key)
    {
        unset($this->items[$key]);
    }

    /**
     * {@inheritdoc}
     */
    public function keys()
    {
        return \array_keys($this->items);
    }
}
