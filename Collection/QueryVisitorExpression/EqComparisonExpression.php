<?php

namespace Videni\Bundle\RestBundle\Collection\QueryVisitorExpression;

use Videni\Bundle\RestBundle\Collection\QueryExpressionVisitor;

/**
 * Represents EQUAL TO comparison expression.
 */
class EqComparisonExpression implements ComparisonExpressionInterface
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
        if (null === $value) {
            return $visitor->getExpressionBuilder()->isNull($expression);
        }

        $visitor->addParameter($parameterName, $value);

        return $visitor->getExpressionBuilder()
            ->eq($expression, $visitor->buildPlaceholder($parameterName));
    }
}
