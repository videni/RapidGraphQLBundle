<?php

namespace App\Bundle\RestBundle\Collection\QueryVisitorExpression;

use App\Bundle\RestBundle\Collection\QueryExpressionVisitor;

/**
 * Represents NOT LIKE '%value' comparison expression.
 */
class NotEndsWithComparisonExpression implements ComparisonExpressionInterface
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
        $visitor->addParameter($parameterName, '%' . $value);

        return $visitor->getExpressionBuilder()
            ->notLike($expression, $visitor->buildPlaceholder($parameterName));
    }
}
