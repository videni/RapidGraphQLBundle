<?php

namespace Videni\Bundle\RestBundle\Filter\Normalizer;

/**
 * Converts a string to string (actually a value is kept as is
 * because a sting value does not required any transformation).
 * Provides a regular expression that can be used to validate a string value.
 */
class StringNormalizer extends AbstractNormalizer
{
    const REQUIREMENT = '.+';

    /**
     * {@inheritdoc}
     */
    protected function getDataTypeString()
    {
        return 'string';
    }

    /**
     * {@inheritdoc}
     */
    protected function getDataTypePluralString()
    {
        return 'strings';
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
    public function processRequirement(NormalizerContext $context)
    {
        $context->setRequirement($this->getRequirement());
    }

    /**
     * {@inheritdoc}
     */
    protected function normalizeValue($value)
    {
        return $value;
    }
}
