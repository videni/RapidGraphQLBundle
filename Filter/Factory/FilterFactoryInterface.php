<?php

namespace App\Bundle\RestBundle\Filter\Factory;

interface FilterFactoryInterface
{
    /**
     * Creates a new instance of filter.
     *
     * @param string $filterType The type of a filter.
     * @param array  $options    The filter options.
     *
     * @return StandaloneFilter|null
     */
    public function createFilter($filterType, array $options = []);
}
