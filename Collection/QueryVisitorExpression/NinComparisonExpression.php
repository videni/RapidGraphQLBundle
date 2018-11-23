<?php

namespace App\Bundle\RestBundle\Collection\QueryVisitorExpression;

use App\Bundle\RestBundle\Collection\QueryExpressionVisitor;

/**
 * Represents NOT IN comparison expression.
 */
class NinComparisonExpression implements ComparisonExpressionInterface
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
        $visitor->addParameter($parameterName, $value);

        return $visitor->getExpressionBuilder()
            ->notIn($expression, $visitor->buildPlaceholder($parameterName));
    }
}
