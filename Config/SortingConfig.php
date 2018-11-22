<?php

namespace App\Bundle\RestBundle\Config;

/**
 * Represents the configuration of a field that can be used to filter data.
 */
class SortConfig implements ConfigBagInterface
{
    public const PROPERTY_PATH = 'property_path';
    public const DESCRIPTION = 'description';
    public const ORDER = 'order';

    private $items = [];

    /**
     * Gets a native PHP array representation of the configuration.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->items;
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
     * Indicates whether the description attribute exists.
     *
     * @return bool
     */
    public function hasOrder()
    {
        return $this->has(self::ORDER);
    }

    /**
     * Gets the value of the Order attribute.
     *
     * @return string|Label|null
     */
    public function getOrder()
    {
        return $this->get(self::ORDER);
    }

    /**
     * Sets the value of the Order attribute.
     *
     * @param string|Label|null $Order
     */
    public function setOrder($Order)
    {
        if ($Order) {
            $this->items[self::ORDER] = $Order;
        } else {
            unset($this->items[self::ORDER]);
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
}
