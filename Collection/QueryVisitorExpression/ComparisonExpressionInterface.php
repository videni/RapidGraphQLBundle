<?php

namespace App\Bundle\RestBundle\Collection\QueryVisitorExpression;

use App\Bundle\RestBundle\Collection\QueryExpressionVisitor;

/**
 * Provides an interface for different kind of comparison expressions.
 */
interface ComparisonExpressionInterface
{
    /**
     * Builds a comparison expression.
     *
     * @param QueryExpressionVisitor $visitor
     * @param string                 $expression
     * @param string                 $parameterName
     * @param mixed                  $value
     *
     * @return mixed
     */
    public function walkComparisonExpression(
        QueryExpressionVisitor $visitor,
        string $expression,
        string $parameterName,
        $value
    );
}
