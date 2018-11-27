<?php

namespace App\Bundle\RestBundle\Processor\Shared;

use App\Bundle\RestBundle\Filter\FilterInterface;
use App\Bundle\RestBundle\Filter\StandaloneFilterWithDefaultValue;
use App\Bundle\RestBundle\Processor\Context;
use Oro\Component\ChainProcessor\ContextInterface;
use Oro\Component\ChainProcessor\ProcessorInterface;

/**
 * Applies all requested filters to the Criteria object.
 */
class BuildCriteria implements ProcessorInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContextInterface $context)
    {
        /** @var Context $context */

        if ($context->hasQuery()) {
            // a query is already built
            return;
        }

        $criteria = $context->getCriteria();
        if (null === $criteria) {
            // the criteria object does not exist
            return;
        }

        /** @var FilterInterface[] $filters */
        $filters = $context->getFilters();
        $filterValues = $context->getFilterValues();

        /**
         * it is important to iterate by $filters, not by $filterValues,
         * because the the order of filters is matter,
         * e.g. "page size" filter should be processed before "page number" filter
         * @see \App\Bundle\RestBundle\Processor\Shared\SetDefaultPaging::addPageNumberFilter
         */
        foreach ($filters as $filterKey => $filter) {
            if ($filterValues->has($filterKey)) {
                $filterValue = $filterValues->get($filterKey);
                try {
                    $filter->apply($criteria, $filterValue);
                } catch (\Exception $e) {

                }
            } elseif ($filter instanceof StandaloneFilterWithDefaultValue) {
                $filter->apply($criteria);
            }
        }
    }
}
