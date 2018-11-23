<?php

namespace App\Bundle\RestBundle\Collection;

use App\Bundle\RestBundle\Collection\QueryVisitorExpression\ComparisonExpressionInterface;
use App\Bundle\RestBundle\Collection\QueryVisitorExpression\CompositeExpressionInterface;

class QueryExpressionVisitorFactory
{
    /** @var CompositeExpressionInterface[] */
    private $compositeExpressions = [];

    /** @var ComparisonExpressionInterface[] */
    private $comparisonExpressions = [];

    /**
     * @param CompositeExpressionInterface[]  $compositeExpressions  [type => expression, ...]
     * @param ComparisonExpressionInterface[] $comparisonExpressions [operator => expression, ...]
     */
    public function __construct(array $compositeExpressions = [], array $comparisonExpressions = [])
    {
        $this->compositeExpressions = $compositeExpressions;
        $this->comparisonExpressions = $comparisonExpressions;
    }

    /**
     * Creates a new instance of QueryExpressionVisitor.
     *
     * @return QueryExpressionVisitor
     */
    public function createExpressionVisitor()
    {
        return new QueryExpressionVisitor($this->compositeExpressions, $this->comparisonExpressions);
    }
}
