<?php

namespace Videni\Bundle\RestBundle\Filter\FilterValue;

/**
 * Implements empty collection of the FilterValue objects.
 */
class NullFilterValueAccessor implements FilterValueAccessorInterface
{
    /**
     * {@inheritdoc}
     */
    public function has($key)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function get($key)
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function remove($key)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getGroup($group)
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultGroupName()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultGroupName($group)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getAll()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function set($key, FilterValue $value = null)
    {
    }
}
