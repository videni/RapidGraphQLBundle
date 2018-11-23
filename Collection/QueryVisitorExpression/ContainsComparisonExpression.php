<?php

namespace App\Bundle\RestBundle\Collection\QueryVisitorExpression;

use App\Bundle\RestBundle\Collection\QueryExpressionVisitor;

/**
 * Represents LIKE '%value%' comparison expression.
 */
class ContainsComparisonExpression implements ComparisonExpressionInterface
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
        $visitor->addParameter($parameterName, '%' . $value . '%');

        return $visitor->getExpressionBuilder()
            ->like($expression, $visitor->buildPlaceholder($parameterName));
    }
}
