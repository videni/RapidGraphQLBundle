<?php

namespace App\Bundle\RestBundle\Collection\QueryVisitorExpression;

use App\Bundle\RestBundle\Collection\QueryExpressionVisitor;

/**
 * Represents EMPTY (to-many association does not contain any records)
 * and NOT EMPTY (to-many association contains at least one record) comparison expressions.
 */
class EmptyComparisonExpression implements ComparisonExpressionInterface
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
        $builder = $visitor->getExpressionBuilder();

        $subquery = $visitor->createSubquery($field);
        $expr = $builder->exists($subquery->getDQL());
        if ($value) {
            $expr = $builder->not($expr);
        }

        return $expr;
    }
}
