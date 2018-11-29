<?php

declare(strict_types=1);

namespace Videni\Bundle\RestBundle\Processor\Shared;

use Oro\Component\ChainProcessor\ContextInterface;
use Oro\Component\ChainProcessor\ProcessorInterface;
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

final class LoadResources implements ProcessorInterface
{
    private $filterNames;

    private $pagerfantaRepresentationFactory;

    public function __construct(PagerfantaFactory $pagerfantaRepresentationFactory, FilterNames $filterNames)
    {
        $this->pagerfantaRepresentationFactory = $pagerfantaRepresentationFactory;
            $this->filterNames = $filterNames;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContextInterface $context)
    {
        /** @var IndexContext $context */

        if ($context->hasResult()) {
            // result data are already retrieved
            return;
        }

        $query = $context->getQuery();

        if ($query instanceof QueryBuilder) {
            $paginatorConfig = $context->getPaginatorConfig();
            if (null == $paginatorConfig || -1 === $paginatorConfig->getMaxResults()) {
                // the paging is disabled
                $context->setResult($query->getQuery()->getResult());

                return ;
            }
            $paginator = $this->getPaginator($query);

            $this->addPaging($context->getFilterValues(), $paginator, $paginatorConfig);

            $request = $context->getRequest();
            $route = new Route($request->attributes->get('_route'), array_merge($request->attributes->get('_route_params'), $request->query->all()));

            // This prevents Pagerfanta from querying database from a template
            $paginator->getCurrentPageResults();

            $paginatedRepresentation = $this->pagerfantaRepresentationFactory->createRepresentation($paginator, $route);

            $context->setResult($paginatedRepresentation);
        }
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

    public function addPaging(FilterValueAccessor $filterValues, Pagerfanta $paginator, PaginatorConfig $paginatorConfig = null)
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
