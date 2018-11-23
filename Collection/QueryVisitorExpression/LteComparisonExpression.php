<?php

namespace App\Bundle\RestBundle\Collection\QueryVisitorExpression;

use App\Bundle\RestBundle\Collection\QueryExpressionVisitor;

/**
 * Represents LESS THAN OR EQUAL TO comparison expression.
 */
class LteComparisonExpression implements ComparisonExpressionInterface
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
            ->lte($expression, $visitor->buildPlaceholder($parameterName));
    }
}
