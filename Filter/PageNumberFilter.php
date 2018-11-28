<?php

namespace App\Bundle\RestBundle\Filter;

use Doctrine\Common\Collections\Criteria;
use Oro\Component\DoctrineUtils\ORM\QueryBuilderUtil;
use App\Bundle\RestBundle\Filter\FilterValue\FilterValue;

/**
 * A filter that can be used to specify the page number.
 */
class PageNumberFilter extends StandaloneFilterWithDefaultValue
{
    /**
     * {@inheritdoc}
     */
    public function apply(Criteria $criteria, FilterValue $value = null)
    {
        $val = null !== $value
            ? $value->getValue()
            : $this->getDefaultValue();

        if (null !== $val) {
            $pageSize = $criteria->getMaxResults();
            if (null !== $pageSize) {
                $criteria->setFirstResult(QueryBuilderUtil::getPageOffset($val, $pageSize));
            }
        }
    }
}
