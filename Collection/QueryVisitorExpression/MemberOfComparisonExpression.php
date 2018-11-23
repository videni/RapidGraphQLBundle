<?php

namespace App\Bundle\RestBundle\Collection\QueryVisitorExpression;

use App\Bundle\RestBundle\Collection\QueryExpressionVisitor;

/**
 * Represents MEMBER OF comparison expression.
 */
class MemberOfComparisonExpression implements ComparisonExpressionInterface
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
            ->isMemberOf($visitor->buildPlaceholder($parameterName), $expression);
    }
}
