<?php

namespace Videni\Bundle\RestBundle\Collection\QueryVisitorExpression;

use Videni\Bundle\RestBundle\Collection\QueryExpressionVisitor;

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
        string $field,
        string $expression,
        string $parameterName,
        $value
    ) {
        $visitor->addParameter($parameterName, $value);

        return $visitor->getExpressionBuilder()
            ->notIn($expression, $visitor->buildPlaceholder($parameterName));
    }
}
