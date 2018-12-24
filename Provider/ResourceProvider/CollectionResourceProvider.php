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
use Videni\Bundle\RestBundle\Config\Paginator\PaginatorConfig;
use Videni\Bundle\RestBundle\Filter\FilterValue\FilterValueAccessor;
use Videni\Bundle\RestBundle\Context\ResourceContext;
use Videni\Bundle\RestBundle\Filter\FilterValue\FilterValueAccessorFactory;
use Videni\Bundle\RestBundle\Paginator\PaginatorApplicator;
use Videni\Bundle\RestBundle\Operation\ActionTypes;

class CollectionResourceProvider implements ResourceProviderInterface
{
    private $filterNames;

    private $pagerfantaRepresentationFactory;

    private $paginatorApplicator;

    private $filterValueAccessorFactory;

    public function __construct(
        PagerfantaFactory $pagerfantaRepresentationFactory,
        FilterNames $filterNames,
        PaginatorApplicator $paginatorApplicator,
        FilterValueAccessorFactory $filterValueAccessorFactory
    ) {
        $this->pagerfantaRepresentationFactory = $pagerfantaRepresentationFactory;
        $this->filterNames = $filterNames;
        $this->paginatorApplicator = $paginatorApplicator;
        $this->filterValueAccessorFactory = $filterValueAccessorFactory;
    }

    public function get(ResourceContext $context, Request $request)
    {
        if (!in_array($context->getAction(), [ActionTypes::INDEX])) {
            return;
        }

        $filterValues = $this->filterValueAccessorFactory->create($request);

        $query = $this->paginatorApplicator->apply($context, $filterValues, $request);

        $paginatorConfig = $this->paginatorApplicator->getPaginatorConfig();

        $paginator = $this->getPaginator($query);

        //we have to add paging filter here for we have to set pagination info for Pagerfanta
        $this->addPaging($filterValues, $paginator, $paginatorConfig);

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

    protected function addPaging(FilterValueAccessor $filterValues, Pagerfanta $paginator, PaginatorConfig $paginatorConfig = null)
    {
        $paginator
            ->setAllowOutOfRangePages(true)
            ->setMaxPerPage($paginatorConfig->getMaxResults())
        ;

        $pageNumberFilterName = $this->filterNames->getPageNumberFilterName();
        if ($filterValues->has($pageNumberFilterName)) {
            $paginator->setCurrentPage($filterValues->get($pageNumberFilterName)->getValue());
        }

        $pageSizeFilterName = $this->filterNames->getPageSizeFilterName();
        if ($filterValues->has($pageSizeFilterName)) {
            $customPageSize = $filterValues->get($pageSizeFilterName)->getValue();

            $paginator->setMaxPerPage($customPageSize > $paginatorConfig->getMaxResults() ? $paginatorConfig->getMaxResults(): $customPageSize);
        }
    }
}
