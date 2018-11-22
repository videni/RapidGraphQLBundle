<?php

namespace App\Bundle\RestBundle\Config;

/**
 * Represents the configuration of a field that can be used to filter data.
 */
class FilterConfig implements ConfigBagInterface
{
    const TYPE = 'type';
    const ALLOW_ARRAY = 'allow_array';
    const ALLOW_RANGE = 'allow_range';
    const DESCRIPTION = 'description';
    const FILTER_TYPE = 'filter_type';
    const FILTER_OPTIONS = 'filter_options';
    const PROPERTY_PATH = 'property_path';
    const FILTER_OPERATORS = 'operators';

    /** @var array */
    protected $items = [];

    /**
     * Gets a native PHP array representation of the configuration.
     *
     * @return array
     */
    public function toArray()
    {
        $result = ConfigHelper::convertItemsToArray($this->items);

        if (isset($result[self::ALLOW_ARRAY]) && false === $result[self::ALLOW_ARRAY]) {
            unset($result[self::ALLOW_ARRAY]);
        }
        if (isset($result[self::ALLOW_RANGE]) && false === $result[self::ALLOW_RANGE]) {
            unset($result[self::ALLOW_RANGE]);
        }

        return $result;
    }

    /**
     * Makes a deep copy of the object.
     */
    public function __clone()
    {
        $this->items = ConfigHelper::cloneItems($this->items);
    }

    /**
     * {@inheritdoc}
     */
    public function has($key)
    {
        return \array_key_exists($key, $this->items);
    }

    /**
     * {@inheritdoc}
     */
    public function get($key, $defaultValue = null)
    {
        if (!\array_key_exists($key, $this->items)) {
            return $defaultValue;
        }

        return $this->items[$key];
    }

    /**
     * {@inheritdoc}
     */
    public function set($key, $value)
    {
        if (null !== $value) {
            $this->items[$key] = $value;
        } else {
            unset($this->items[$key]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function remove($key)
    {
        unset($this->items[$key]);
    }

    /**
     * {@inheritdoc}
     */
    public function keys()
    {
        return \array_keys($this->items);
    }

    /**
     * Indicates whether the description attribute exists.
     *
     * @return bool
     */
    public function hasDescription()
    {
        return $this->has(self::DESCRIPTION);
    }

    /**
     * Gets the value of the description attribute.
     *
     * @return string|Label|null
     */
    public function getDescription()
    {
        return $this->get(self::DESCRIPTION);
    }

    /**
     * Sets the value of the description attribute.
     *
     * @param string|Label|null $description
     */
    public function setDescription($description)
    {
        if ($description) {
            $this->items[self::DESCRIPTION] = $description;
        } else {
            unset($this->items[self::DESCRIPTION]);
        }
    }

    /**
     * Indicates whether the path of the field value exists.
     *
     * @return string
     */
    public function hasPropertyPath()
    {
        return $this->has(self::PROPERTY_PATH);
    }

    /**
     * Gets the path of the field value.
     *
     * @param string|null $defaultValue
     *
     * @return string|null
     */
    public function getPropertyPath($defaultValue = null)
    {
        if (empty($this->items[self::PROPERTY_PATH])) {
            return $defaultValue;
        }

        return $this->items[self::PROPERTY_PATH];
    }

    /**
     * Sets the path of the field value.
     *
     * @param string|null $propertyPath
     */
    public function setPropertyPath($propertyPath = null)
    {
        if ($propertyPath) {
            $this->items[self::PROPERTY_PATH] = $propertyPath;
        } else {
            unset($this->items[self::PROPERTY_PATH]);
        }
    }

    /**
     * Indicates whether the data type is set.
     *
     * @return bool
     */
    public function hasType()
    {
        return null !== $this->type;
    }

    /**
     * Gets expected data type of the filter value.
     *
     * @return string|null
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Sets expected data type of the filter value.
     *
     * @param string|null $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * Gets the filter options.
     *
     * @return array|null
     */
    public function getOptions()
    {
        return $this->get(self::FILTER_OPTIONS);
    }

    /**
     * Sets the filter options.
     *
     * @param array|null $options
     */
    public function setOptions($options)
    {
        if ($options) {
            $this->items[self::FILTER_OPTIONS] = $options;
        } else {
            unset($this->items[self::FILTER_OPTIONS]);
        }
    }

    /**
     * Gets a list of operators supported by the filter.
     *
     * @return string[]|null
     */
    public function getOperators()
    {
        return $this->get(self::FILTER_OPERATORS);
    }

    /**
     * Sets a list of operators supported by the filter.
     *
     * @param string[]|null $operators
     */
    public function setOperators($operators)
    {
        if ($operators) {
            $this->items[self::FILTER_OPERATORS] = $operators;
        } else {
            unset($this->items[self::FILTER_OPERATORS]);
        }
    }

    /**
     * Indicates whether the "array allowed" flag is set explicitly.
     *
     * @return bool
     */
    public function hasArrayAllowed()
    {
        return $this->has(self::ALLOW_ARRAY);
    }

    /**
     * Indicates whether the filter value can be an array.
     *
     * @return bool
     */
    public function isArrayAllowed()
    {
        return $this->get(self::ALLOW_ARRAY, false);
    }

    /**
     * Sets a flag indicates whether the filter value can be an array.
     *
     * @param bool $allowArray
     */
    public function setArrayAllowed($allowArray = true)
    {
        $this->items[self::ALLOW_ARRAY] = $allowArray;
    }

    /**
     * Indicates whether the "range allowed" flag is set explicitly.
     *
     * @return bool
     */
    public function hasRangeAllowed()
    {
        return $this->has(self::ALLOW_RANGE);
    }

    /**
     * Indicates whether the filter value can be a pair of "from" and "to" values.
     *
     * @return bool
     */
    public function isRangeAllowed()
    {
        return $this->get(self::ALLOW_RANGE, false);
    }

    /**
     * Sets a flag indicates whether the filter value can be a pair of "from" and "to" values.
     *
     * @param bool $allowRange
     */
    public function setRangeAllowed($allowRange = true)
    {
        $this->items[self::ALLOW_RANGE] = $allowRange;
    }
}
