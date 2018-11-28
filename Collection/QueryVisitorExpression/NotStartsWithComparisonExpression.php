<?php

namespace Videni\Bundle\RestBundle\Collection\QueryVisitorExpression;

use Videni\Bundle\RestBundle\Collection\QueryExpressionVisitor;

/**
 * Represents NOT LIKE 'value%' comparison expression.
 */
class NotStartsWithComparisonExpression implements ComparisonExpressionInterface
{
    /**
     * {@inheritdoc}
     */
    public function walkComparisonExpression(
        QueryExpressionVisitor $visitor,
        string $field,
        string $expression,
        string $parameterName,
        $value
    ) {
        $visitor->addParameter($parameterName, $value . '%');

        return $visitor->getExpressionBuilder()
            ->notLike($expression, $visitor->buildPlaceholder($parameterName));
    }
}
