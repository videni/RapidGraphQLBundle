<?php

namespace App\Bundle\RestBundle\Processor\Shared;

use App\Bundle\RestBundle\Filter\FilterCollection;
use App\Bundle\RestBundle\Filter\FilterNamesRegistry;
use App\Bundle\RestBundle\Filter\PageNumberFilter;
use App\Bundle\RestBundle\Filter\PageSizeFilter;
use App\Bundle\RestBundle\Processor\Context;
use App\Bundle\RestBundle\Model\DataType;
use Oro\Component\ChainProcessor\ContextInterface;
use Oro\Component\ChainProcessor\ProcessorInterface;
use App\Bundle\RestBundle\Filter\FilterNames;
use App\Bundle\RestBundle\Config\Paginator\PaginatorConfig;

/**
 * Sets default paging for different kind of requests.
 * The default page number is 1, the default page size is 10.
 */
class AddPaging implements ProcessorInterface
{
    private const DEFAULT_PAGE_SIZE = 15;

    private $filterNames;

    /**
     * @param FilterNamesRegistry $filterNamesRegistry
     */
    public function __construct(FilterNames $filterNames)
    {
        $this->filterNames = $filterNames;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContextInterface $context)
    {
        /** @var Context $context */

        if ($context->hasQuery()) {
            // a query is already built
            return;
        }

        $pageSize = null;
        $paginatorConfig = $context->getPaginatorConfig();
        if (null !== $paginatorConfig) {
            $pageSize = $paginatorConfig->getPageSize();
        }
        if (-1 === $pageSize) {
            // the paging is disabled
            return;
        }

        $filters = $context->getFilters();
        $this->addPageSizeFilter($this->filterNames->getPageSizeFilterName(), $filters, $pageSize);
        $this->addPageNumberFilter($this->filterNames->getPageNumberFilterName(), $filters);
    }

    /**
     * @param string           $filterName
     * @param FilterCollection $filters
     */
    protected function addPageNumberFilter(string $filterName, FilterCollection $filters)
    {
        /**
         * "page number" filter must be added after "page size" filter because it depends on this filter
         * @see \App\Bundle\RestBundle\Filter\PageNumberFilter::apply
         */
        if (!$filters->has($filterName)) {
            $filters->add(
                $filterName,
                new PageNumberFilter(
                    DataType::UNSIGNED_INTEGER,
                    'The page number, starting from 1.',
                    1
                )
            );
        } else {
            // make sure that "page number" filter is added after "page size" filter
            $pageFilter = $filters->get($filterName);
            $filters->remove($filterName);
            $filters->add($filterName, $pageFilter);
        }
    }

    /**
     * @param string           $filterName
     * @param FilterCollection $filters
     * @param int|null         $pageSize
     */
    protected function addPageSizeFilter(string $filterName, FilterCollection $filters, $pageSize)
    {
        if (!$filters->has($filterName)) {
            $filters->add(
                $filterName,
                new PageSizeFilter(
                    DataType::INTEGER,
                    'The number of items per page.',
                    null !== $pageSize ? $pageSize : $this->getDefaultPageSize()
                )
            );
        }
    }

    /**
     * @return int
     */
    protected function getDefaultPageSize()
    {
        return self::DEFAULT_PAGE_SIZE;
    }
}
