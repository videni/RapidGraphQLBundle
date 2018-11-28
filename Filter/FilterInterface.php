<?php

namespace Videni\Bundle\RestBundle\Filter;

use Doctrine\Common\Collections\Criteria;
use Videni\Bundle\RestBundle\Filter\FilterValue\FilterValue;

/**
 * Provides an interface for different kind of data filters.
 */
interface FilterInterface
{
    /**
     * Applies the filter to the Criteria object.
     *
     * @param Criteria         $criteria
     * @param FilterValue|null $value
     */
    public function apply(Criteria $criteria, FilterValue $value = null);
}
