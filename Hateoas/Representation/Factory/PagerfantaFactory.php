<?php

declare(strict_types=1);

namespace Videni\Bundle\RestBundle\Hateoas\Representation\Factory;

use Hateoas\Configuration\Route;
use Hateoas\Representation\CollectionRepresentation;
use Videni\Bundle\RestBundle\Hateoas\Representation\TotalDisabledPaginatedRepresentation;
use Pagerfanta\Pagerfanta;
use Pintushi\Bundle\GridBundle\Grid\GridInterface;
use Hateoas\Representation\PaginatedRepresentation;

class PagerfantaFactory
{
      /**
     * @var string
     */
    private $pageParameterName;

    /**
     * @var string
     */
    private $limitParameterName;

    public function __construct(?string $pageParameterName = null, ?string $limitParameterName = null)
    {
        $this->pageParameterName  = $pageParameterName;
        $this->limitParameterName = $limitParameterName;
    }

    /**
     * @param Pagerfanta $pager  The pager
     * @param Route      $route  The collection's route
     * @param GrindInterface      $grid
     */
    public function createRepresentation(Pagerfanta $pager, Route $route, GridInterface $grid)
    {
        if ($grid->getConfig()->isTotalsDisabled()) {
            return $this->createTotalDiabledRepresentation($pager, $route, $grid);
        }

        return $this->createNormalRepresentation($pager, $route);
    }

    protected function createTotalDiabledRepresentation(Pagerfanta $pager, Route $route, GridInterface $grid)
    {
        $perPage = $pager->getMaxPerPage();
        $results = $pager->getCurrentPageResults();

        $hasMore = count($results) === $perPage;
        $inline = new CollectionRepresentation($hasMore ? array_slice($results, $perPage-1): $results);

        return new TotalDisabledPaginatedRepresentation(
            $inline,
            $route->getName(),
            $route->getParameters(),
            $pager->getCurrentPage(),
            $perPage-1,
            $this->getPageParameterName(),
            $this->getLimitParameterName(),
            $route->isAbsolute(),
            $hasMore
        );
    }

    /**
     * @param Pagerfanta $pager  The pager
     * @param Route      $route  The collection's route
     * @param mixed      $inline Most of the time, a custom `CollectionRepresentation` instance
     */
    protected function createNormalRepresentation(Pagerfanta $pager, Route $route, $inline = null): PaginatedRepresentation
    {
        if (null === $inline) {
            $inline = new CollectionRepresentation($pager->getCurrentPageResults());
        }

        return new PaginatedRepresentation(
            $inline,
            $route->getName(),
            $route->getParameters(),
            $pager->getCurrentPage(),
            $pager->getMaxPerPage(),
            $pager->getNbPages(),
            $this->getPageParameterName(),
            $this->getLimitParameterName(),
            $route->isAbsolute(),
            $pager->getNbResults()
        );
    }

    public function getPageParameterName(): ?string
    {
        return $this->pageParameterName;
    }

    public function getLimitParameterName(): ?string
    {
        return $this->limitParameterName;
    }
}
