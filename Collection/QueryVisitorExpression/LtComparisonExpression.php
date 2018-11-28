<?php

namespace Videni\Bundle\RestBundle\Collection\QueryVisitorExpression;

use Videni\Bundle\RestBundle\Collection\QueryExpressionVisitor;

/**
 * Represents LESS THAN comparison expression.
 */
class LtComparisonExpression implements ComparisonExpressionInterface
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
            ->lt($expression, $visitor->buildPlaceholder($parameterName));
    }
}
