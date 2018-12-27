<?php

namespace Videni\Bundle\RestBundle\Filter\Normalizer;

use Doctrine\Common\Collections\Criteria;

/**
 * Converts a string represents "orderBy" type to an associative array.
 * The array has the following schema: [field name => sorting direction, ...].
 * Expected format of a string value: field1,-field2,...
 * The "-" is used as shortcut for DESC.
 * Provides a regular expression that can be used to validate that a string represents a value of the "orderBy" type.
 */
class OrderByNormalizer implements FilterValueNormalizerInterface
{
    const REQUIREMENT = '-?[\w\.]+(,-?[\w\.]+)*';

    public function getDataTypeString()
    {
        return 'order by';
    }

    /**
     * {@inheritdoc}
     */
    public function normalize(NormalizerContext $context)
    {
        /** @var NormalizerContext $context */

        if (!$context->getRequirement()) {
            $context->setRequirement(self::REQUIREMENT);
        }
        if ($value = $context->getResult()) {
            if (null !== $value && is_string($value)) {
                $orderBy = [];
                $items   = explode(',', $value);
                foreach ($items as $item) {
                    $item = trim($item);
                    if (0 === strpos($item, '-')) {
                        $orderBy[substr($item, 1)] = Criteria::DESC;
                    } else {
                        $orderBy[$item] = Criteria::ASC;
                    }
                }
                $context->setResult($orderBy);
            }
        }
    }
}
