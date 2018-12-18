<?php

namespace Videni\Bundle\RestBundle\Paginator;

use Symfony\Component\HttpFoundation\Request;
use Videni\Bundle\RestBundle\Filter\FilterValue\FilterValueAccessor;
use Videni\Bundle\RestBundle\Util\DoctrineHelper;
use Videni\Bundle\RestBundle\Util\EntityClassResolver;
use Videni\Bundle\RestBundle\Collection\Criteria;
use Videni\Bundle\RestBundle\Filter\StandaloneFilterWithDefaultValue;
use Videni\Bundle\RestBundle\Context\ResourceContext;

class PaginatorApplicator
{
     /** @var DoctrineHelper */
    protected $doctrineHelper;

    /** @var EntityClassResolver */
    protected $entityClassResolver;

    protected $buildQuery;

    protected $registerConfiguredFilter;

    private $addSorting;

    private $validateSorting;

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
        ValidateSorting $validateSorting
    ) {
        $this->doctrineHelper = $doctrineHelper;
        $this->entityClassResolver = $entityClassResolver;
        $this->buildQuery = $buildQuery;
        $this->registerConfiguredFilter = $registerConfiguredFilter;
        $this->addSorting = $addSorting;
        $this->validateSorting = $validateSorting;
    }

    public function apply(ResourceContext $resourceContext, FilterValueAccessor $filterValues)
    {
        $criteria = new Criteria($this->entityClassResolver);

        $paginatorConfig = $this->getPaginatorConifig($resourceContext);
        if ($paginatorConfig) {
            $this->applyFilters($criteria, $paginatorConfig);
        }

        $query = $this->buildQuery->build($request, $resourceContext, $criteria);
    }

    protected function applyFilters($criteria, $paginatorConfig)
    {
        /** @var FilterInterface[] $filters */
        $filters = $this->registerConfiguredFilter->getFilters($resourceContext, $paginatorConfig);

        $this->addSorting->process($resourceContext->getClassName(), $paginatorConfig, $filters);

        $this->validateSorting->validate($filters, $filterValues, $paginatorConfig);

        /**
         * it is important to iterate by $filters, not by $filterValues,
         * because the the order of filters is matter,
         * e.g. "page size" filter should be processed before "page number" filter
         * @see \Videni\Bundle\RestBundle\Paginator\SetDefaultPaging::addPageNumberFilter
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

    protected function getPaginatorConifig(ResourceContext $context)
    {
        $operationName = $context->getOperationName();
        $paginator = $resourceConfig->getOperation($operationName)->getPaginator();
        if (!$paginator || !$resourceConfig->hasPaginator($paginator)) {
            return;
        }

        return $resourceConfig->getPaginator($paginator);
    }
}
