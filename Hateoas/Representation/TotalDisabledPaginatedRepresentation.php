<?php

declare(strict_types=1);

namespace Videni\Bundle\RestBundle\Hateoas\Representation;

use Hateoas\Configuration\Annotation as Hateoas;
use JMS\Serializer\Annotation as Serializer;

/**
 * @Serializer\ExclusionPolicy("all")
 * @Serializer\XmlRoot("collection")
 * @Serializer\AccessorOrder("custom", custom = {"page", "limit"})
 *
 * @Hateoas\Relation(
 *      "first",
 *      href = @Hateoas\Route(
 *          "expr(object.getRoute())",
 *          parameters = "expr(object.getParameters(1))",
 *          absolute = "expr(object.isAbsolute())"
 *      )
 * )
 * @Hateoas\Relation(
 *      "next",
 *      href = @Hateoas\Route(
 *          "expr(object.getRoute())",
 *          parameters = "expr(object.getParameters(object.getPage() + 1))",
 *          absolute = "expr(object.isAbsolute())"
 *      ),
 *      exclusion = @Hateoas\Exclusion(
 *          excludeIf = "expr(false === object.hasMore())"
 *      )
 * )
 * @Hateoas\Relation(
 *      "previous",
 *      href = @Hateoas\Route(
 *          "expr(object.getRoute())",
 *          parameters = "expr(object.getParameters(object.getPage() - 1))",
 *          absolute = "expr(object.isAbsolute())"
 *      ),
 *      exclusion = @Hateoas\Exclusion(
 *          excludeIf = "expr((object.getPage() - 1) < 1)"
 *      )
 * )
 */
class TotalDisabledPaginatedRepresentation extends AbstractSegmentedRepresentation
{
    /**
     * @Serializer\Expose
     * @Serializer\Type("integer")
     * @Serializer\XmlAttribute
     *
     * @var int
     */
    private $page;

    /**
     * @var string
     */
    private $pageParameterName;

    private $hasMore;

    /**
     * @param mixed $inline
     */
    public function __construct(
        $inline,
        string $route,
        array $parameters = [],
        ?int $page,
        ?int $limit,
        ?string $pageParameterName = null,
        ?string $limitParameterName = null,
        bool $absolute = false,
        bool $hasMore = false
    ) {
        parent::__construct($inline, $route, $parameters, $limit, $limitParameterName, $absolute);

        $this->page               = $page;
        $this->hasMore             = $hasMore;
        $this->pageParameterName  = $pageParameterName  ?: 'page';
    }

    public function getPage(): int
    {
        return $this->page;
    }

    /**
     * @param  null  $page
     * @param  null  $limit
     *
     * @return array
     */
    public function getParameters($page = null, $limit = null): array
    {
        $parameters = parent::getParameters($limit);

        unset($parameters[$this->pageParameterName]);
        $parameters[$this->pageParameterName] = $page ?? $this->getPage();

        $this->moveParameterToEnd($parameters, $this->getLimitParameterName());

        return $parameters;
    }

    public function getPages(): int
    {
        return $this->pages;
    }

    public function getPageParameterName(): string
    {
        return $this->pageParameterName;
    }

    public function hasMore()
    {
        return $this->hasMore;
    }
}
