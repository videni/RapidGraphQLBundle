<?php

namespace Videni\Bundle\RestBundle\Filter;

/**
 * Provides names of predefined filters.
 */
class FilterNames
{
    /** @var string */
    private $sortFilterName;

    /** @var string */
    private $pageNumberFilterName;

    /** @var string */
    private $pageSizeFilterName;

    /** @var string|null */
    private $dataFilterGroupName;


    /**
     * @param string      $sortFilterName
     * @param string      $pageNumberFilterName
     * @param string      $pageSizeFilterName
     * @param string|null $dataFilterGroupName
     */
    public function __construct(
        string $sortFilterName,
        string $pageNumberFilterName,
        string $pageSizeFilterName,
        string $dataFilterGroupName = null
    ) {
        $this->sortFilterName = $sortFilterName;
        $this->pageNumberFilterName = $pageNumberFilterName;
        $this->pageSizeFilterName = $pageSizeFilterName;
        $this->dataFilterGroupName = $dataFilterGroupName;
    }

    /**
     * Gets the name of a filter that can be used to specify how a result collection should be sorted.
     * @see \Videni\Bundle\RestBundle\Filter\SortFilter
     *
     * @return string
     */
    public function getSortFilterName(): string
    {
        return $this->sortFilterName;
    }

    /**
     * Gets the name of a filter that can be used to specify the page number.
     * @see \Videni\Bundle\RestBundle\Filter\PageNumberFilter
     *
     * @return string
     */
    public function getPageNumberFilterName(): string
    {
        return $this->pageNumberFilterName;
    }

    /**
     * Gets the name of a filter that can be used to specify the maximum number of records on one page.
     * @see \App\Bundle\ApiBundle\Filter\PageSizeFilter
     *
     * @return string
     */
    public function getPageSizeFilterName(): string
    {
        return $this->pageSizeFilterName;
    }

    public function getDataFilterGroupName(): string
    {
        return $this->dataFilterGroupName;
    }
}
