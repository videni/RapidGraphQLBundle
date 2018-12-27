<?php

namespace Videni\Bundle\RestBundle\Filter\Normalizer;

/**
 * Converts a string to DateTime object.
 * Provides a regular expression that can be used to validate that a string represents a date-time value.
 */
class DateTimeNormalizer extends AbstractNormalizer
{
    const REQUIREMENT = '\d{4}(-\d{2}(-\d{2}([T ]\d{2}:\d{2}(:\d{2}(\.\d+)?)?(Z|([-+]\d{2}(:?\d{2})?))?)?)?)?';

    /**
     * {@inheritdoc}
     */
    protected function getDataTypeString()
    {
        return 'datetime';
    }

    /**
     * {@inheritdoc}
     */
    protected function getDataTypePluralString()
    {
        return 'datetimes';
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
        return new \DateTime($value);
    }
}
