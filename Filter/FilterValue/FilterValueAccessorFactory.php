<?php

namespace App\Bundle\RestBundle\Filter\FilterValue;

use App\Bundle\RestBundle\Filter\FilterValueAccessorInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Creates new instance of the filter value accessor that is used to extract filters from REST API HTTP Request.
 */
class FilterValueAccessorFactory
{
    /** @var string */
    private $operatorPattern;

    /** @var array [operator name => operator, ...] */
    private $operators;

    /**
     * @param string   $operatorPattern
     * @param string[] $operators
     */
    public function __construct($operatorPattern, array $operators)
    {
        $this->operatorPattern = $operatorPattern;
        $this->operators = $operators;
    }


    /**
     * Creates new instance of the filter value accessor.
     *
     * @param Request $request
     *
     * @return FilterValueAccessorInterface
     */
    public function create(Request $request)
    {
        return new FilterValueAccessor(
            $request,
            $this->operatorPattern,
            $this->operators
        );
    }
}
