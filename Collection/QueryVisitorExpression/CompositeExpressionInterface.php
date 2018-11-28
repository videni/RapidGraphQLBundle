<?php

namespace Videni\Bundle\RestBundle\Collection\QueryVisitorExpression;

/**
 * Provides an interface for different kind of composite expressions.
 */
interface CompositeExpressionInterface
{
    /**
     * Builds a composite expression.
     *
     * @param array $expressions
     *
     * @return mixed
     */
    public function walkCompositeExpression(array $expressions);
}
