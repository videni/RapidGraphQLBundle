<?php

namespace App\Bundle\RestBundle\Collection\QueryVisitorExpression;

use App\Bundle\RestBundle\Collection\QueryExpressionVisitor;

/**
 * Represents EXISTS (IS NOT NULL) and NOT EXISTS (IS NULL) comparison expressions.
 */
class ExistsComparisonExpression implements ComparisonExpressionInterface
{
    /**
     * {@inheritdoc}
     */
    public function walkComparisonExpression(
        QueryExpressionVisitor $visitor,
        string $expression,
        string $parameterName,
        $value
    ) {
        if ($value) {
            return $visitor->getExpressionBuilder()->isNotNull($expression);
        }

        return $visitor->getExpressionBuilder()->isNull($expression);
    }
}
