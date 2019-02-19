<?php

declare(strict_types=1);

namespace Videni\Bundle\RestBundle\Provider\ResourceProvider;

use Videni\Bundle\RestBundle\Exception;
use Symfony\Component\HttpFoundation\Request;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Doctrine\ORM\QueryBuilder;
use Hateoas\Representation\Factory\PagerfantaFactory;
use Hateoas\Configuration\Route;
use Videni\Bundle\RestBundle\Filter\FilterNames;
use Videni\Bundle\RestBundle\Config\Grid\Grid;
use Videni\Bundle\RestBundle\Filter\FilterValue\FilterValueAccessor;
use Videni\Bundle\RestBundle\Context\ResourceContext;
use Videni\Bundle\RestBundle\Filter\FilterValue\FilterValueAccessorFactory;
use Videni\Bundle\RestBundle\Grid\GridApplicator;
use Videni\Bundle\RestBundle\Operation\ActionTypes;

class CollectionResourceProvider implements ResourceProviderInterface
{
    const UNLIMITED_RESULT = -1;

    private $filterNames;

    private $pagerfantaRepresentationFactory;

    private $gridApplicator;

    private $filterValueAccessorFactory;

    public function __construct(
        PagerfantaFactory $pagerfantaRepresentationFactory,
        FilterNames $filterNames,
        GridApplicator $gridApplicator,
        FilterValueAccessorFactory $filterValueAccessorFactory
    ) {
        $this->pagerfantaRepresentationFactory = $pagerfantaRepresentationFactory;
        $this->filterNames = $filterNames;
        $this->gridApplicator = $gridApplicator;
        $this->filterValueAccessorFactory = $filterValueAccessorFactory;
    }

    public function get(ResourceContext $context, Request $request)
    {
        if (!in_array($context->getAction(), [ActionTypes::INDEX])) {
            return;
        }

        $filterValues = $this->filterValueAccessorFactory->create($request);

        $query = $this->gridApplicator->apply($context, $filterValues, $request);

        $grid = $context->getGrid();
        if(self::UNLIMITED_RESULT === $grid->getMaxResults()) {
            return $query->getQuery()->getResult();
        }

        $paginator = $this->getPaginator($query);

        //we have to add paging filter here for we have to set pagination info for Pagerfanta
        $this->addPaging($filterValues, $paginator, $grid);

        $route = new Route($request->attributes->get('_route'), array_merge($request->attributes->get('_route_params'), $request->query->all()));

        // This prevents Pagerfanta from querying database from a template
        $paginator->getCurrentPageResults();

        $paginatedRepresentation = $this->pagerfantaRepresentationFactory->createRepresentation($paginator, $route);

        return $paginatedRepresentation;
    }

      /**
     * @param QueryBuilder $queryBuilder
     *
     * @return Pagerfanta
     */
    protected function getPaginator(QueryBuilder $queryBuilder): Pagerfanta
    {
        // Use output walkers option in DoctrineORMAdapter should be false as it affects performance greatly (see #3775)
        return new Pagerfanta(new DoctrineORMAdapter($queryBuilder, false, false));
    }

    protected function addPaging(FilterValueAccessor $filterValues, Pagerfanta $paginator, Grid $grid)
    {
        $paginator
            ->setAllowOutOfRangePages(true)
            ->setMaxPerPage($grid->getMaxResults())
        ;

        $pageNumberFilterName = $this->filterNames->getPageNumberFilterName();
        if ($filterValues->has($pageNumberFilterName)) {
            $paginator->setCurrentPage($filterValues->get($pageNumberFilterName)->getValue());
        }

        $pageSizeFilterName = $this->filterNames->getPageSizeFilterName();
        if ($filterValues->has($pageSizeFilterName)) {
            $customPageSize = $filterValues->get($pageSizeFilterName)->getValue();

            $paginator->setMaxPerPage($customPageSize > $grid->getMaxResults() ? $grid->getMaxResults(): $customPageSize);
        }
    }
}
