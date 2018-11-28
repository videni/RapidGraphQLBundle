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

final class LoadResources implements ProcessorInterface
{
    private $pagerfantaRepresentationFactory;

    public function __construct(PagerfantaFactory $pagerfantaRepresentationFactory)
    {
        $this->pagerfantaRepresentationFactory = $pagerfantaRepresentationFactory;
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
            $paginator = $this->getPaginator($query);

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
}
