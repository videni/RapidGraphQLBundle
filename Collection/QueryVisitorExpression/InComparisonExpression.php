<?php

namespace Videni\Bundle\RestBundle\Collection\QueryVisitorExpression;

use Videni\Bundle\RestBundle\Collection\QueryExpressionVisitor;

/**
 * Represents IN comparison expression.
 */
class InComparisonExpression implements ComparisonExpressionInterface
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
            ->in($expression, $visitor->buildPlaceholder($parameterName));
    }
}
