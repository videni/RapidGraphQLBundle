<?php

namespace App\Bundle\RestBundle\Config\Paginator;

/**
 * Represents the configuration of  paginator
 */
class PaginatorConfig
{
    private $maxResults;
    private $pageSize;
    private $disableSorting;
    private $class;

    /** @var FilterConfig[] */
    protected $filters = [];

    protected $sortings =  [];

    /**
     * @return mixed
     */
    public function getMaxResults()
    {
        return $this->maxResults;
    }

    /**
     * @param mixed $maxResults
     *
     * @return self
     */
    public function setMaxResults($maxResults)
    {
        $this->maxResults = $maxResults;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPageSize()
    {
        return $this->pageSize;
    }

    /**
     * @param mixed $pageSize
     *
     * @return self
     */
    public function setPageSize($pageSize)
    {
        $this->pageSize = $pageSize;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDisableSorting()
    {
        return $this->disableSorting;
    }

    /**
     * @param mixed $disableSorting
     *
     * @return self
     */
    public function setDisableSorting($disableSorting)
    {
        $this->disableSorting = $disableSorting;

        return $this;
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
     * @return mixed
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @param mixed $class
     *
     * @return self
     */
    public function setClass($class)
    {
        $this->class = $class;

        return $this;
    }
}
