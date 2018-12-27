<?php

namespace Videni\Bundle\RestBundle\Filter\Normalizer;

use Videni\Bundle\RestBundle\Filter\Normalizer\AbstractNormalizer;

/**
 * Converts a string to DateTime object (only time part).
 * Provides a regular expression that can be used to validate that a string represents a time value.
 */
class TimeNormalizer extends AbstractNormalizer
{
    const REQUIREMENT = '\d{2}:\d{2}(:\d{2}(\.\d+)?)?';

    /**
     * {@inheritdoc}
     */
    protected function getDataTypeString()
    {
        return 'time';
    }

    /**
     * {@inheritdoc}
     */
    protected function getDataTypePluralString()
    {
        return 'times';
    }

    /**
     * {@inheritdoc}
     */
    protected function getRequirement()
    {
        return self::REQUIREMENT;
    }

    /**
     * {@inheritdoc}
     */
    protected function normalizeValue($value)
    {
        return new \DateTime('1970-01-01T' . $value, new \DateTimeZone('UTC'));
    }
}
