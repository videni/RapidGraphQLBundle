<?php

namespace Videni\Bundle\RestBundle\Collection;

use Videni\Bundle\RestBundle\Collection\QueryVisitorExpression\ComparisonExpressionInterface;
use Videni\Bundle\RestBundle\Collection\QueryVisitorExpression\CompositeExpressionInterface;
use Videni\Bundle\RestBundle\Util\EntityClassResolver;

/**
 * The factory to create a new instance of QueryExpressionVisitor class.
 */
class QueryExpressionVisitorFactory
{
    /** @var CompositeExpressionInterface[] */
    private $compositeExpressions;

    /** @var ComparisonExpressionInterface[] */
    private $comparisonExpressions;

    /** @var EntityClassResolver */
    private $entityClassResolver;

    /**
     * @param CompositeExpressionInterface[]  $compositeExpressions  [type => expression, ...]
     * @param ComparisonExpressionInterface[] $comparisonExpressions [operator => expression, ...]
     * @param EntityClassResolver             $entityClassResolver
     */
    public function __construct(
        array $compositeExpressions,
        array $comparisonExpressions,
        EntityClassResolver $entityClassResolver
    ) {
        $this->compositeExpressions = $compositeExpressions;
        $this->comparisonExpressions = $comparisonExpressions;
        $this->entityClassResolver = $entityClassResolver;
    }

    /**
     * Creates a new instance of QueryExpressionVisitor.
     *
     * @return QueryExpressionVisitor
     */
    public function createExpressionVisitor()
    {
        return new QueryExpressionVisitor(
            $this->compositeExpressions,
            $this->comparisonExpressions,
            $this->entityClassResolver
        );
    }
}
