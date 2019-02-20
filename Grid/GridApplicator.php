<?php

namespace Videni\Bundle\RestBundle\Grid;

use Symfony\Component\HttpFoundation\Request;
use Videni\Bundle\RestBundle\Filter\FilterValue\FilterValueAccessor;
use Videni\Bundle\RestBundle\Util\DoctrineHelper;
use Videni\Bundle\RestBundle\Util\EntityClassResolver;
use Videni\Bundle\RestBundle\Collection\Criteria;
use Videni\Bundle\RestBundle\Filter\StandaloneFilterWithDefaultValue;
use Videni\Bundle\RestBundle\Context\ResourceContext;

class GridApplicator
{
     /** @var DoctrineHelper */
    protected $doctrineHelper;

    /** @var EntityClassResolver */
    protected $entityClassResolver;

    protected $buildQuery;

    protected $registerConfiguredFilter;

    private $addSorting;

    private $validateSorting;

    private $normalizeFilterValues;

    /**
     * @param DoctrineHelper      $doctrineHelper
     * @param EntityClassResolver $entityClassResolver
     */
    public function __construct(
        DoctrineHelper $doctrineHelper,
        EntityClassResolver $entityClassResolver,
        BuildQuery $buildQuery,
        RegisterConfiguredFilter $registerConfiguredFilter,
        AddSorting $addSorting,
        ValidateSorting $validateSorting,
        NormalizeFilterValues $normalizeFilterValues
    ) {
        $this->doctrineHelper = $doctrineHelper;
        $this->entityClassResolver = $entityClassResolver;
        $this->buildQuery = $buildQuery;
        $this->registerConfiguredFilter = $registerConfiguredFilter;
        $this->addSorting = $addSorting;
        $this->validateSorting = $validateSorting;
        $this->normalizeFilterValues = $normalizeFilterValues;
    }

    public function apply(ResourceContext $resourceContext, FilterValueAccessor $filterValues, Request $request)
    {
        $criteria = new Criteria($this->entityClassResolver);

        $this->applyFilters($criteria, $resourceContext, $filterValues);

        return $this->buildQuery->build($criteria, $resourceContext, $request)->getQuery();
    }

    protected function applyFilters(Criteria $criteria, ResourceContext $resourceContext,FilterValueAccessor $filterValues)
    {
        $grid = $resourceContext->getGrid();
        if (null === $grid) {
            return;
        }

        /** @var FilterInterface[] $filters */
        $filters = $this->registerConfiguredFilter->getFilters($resourceContext, $grid);

        $this->addSorting->process($resourceContext->getClassName(), $grid, $filters);
        $this->validateSorting->validate($filters, $filterValues, $grid);
        $this->normalizeFilterValues->normalize($resourceContext, $filters, $filterValues);

        /**
         * it is important to iterate by $filters, not by $filterValues,
         * because the the order of filters is matter,
         * e.g. "page size" filter should be processed before "page number" filter
         * @see \Videni\Bundle\RestBundle\Grid\SetDefaultPaging::addPageNumberFilter
         */
        foreach ($filters as $filterKey => $filter) {
            if ($filterValues->has($filterKey)) {
                $filterValue = $filterValues->get($filterKey);
                try {
                    $filter->apply($criteria, $filterValue);
                } catch (\Exception $e) {
                }
            } elseif ($filter instanceof StandaloneFilterWithDefaultValue) {
                $filter->apply($criteria);
            }
        }
    }
}
