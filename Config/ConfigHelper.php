<?php

namespace App\Bundle\RestBundle\Config;

class ConfigHelper
{
     /**
     * Gets a native PHP array representation of each object in a given array.
     *
     * @param object[] $objects
     * @param bool     $treatEmptyAsNull
     *
     * @return array
     */
    public static function convertObjectsToArray(array $objects, $treatEmptyAsNull = false)
    {
        $result = [];
        foreach ($objects as $key => $value) {
            $arrayValue = $value->toArray();
            if (!empty($arrayValue)) {
                $result[$key] = $arrayValue;
            } elseif ($treatEmptyAsNull) {
                $result[$key] = null;
            }
        }

        return $result;
    }

    /**
     * Gets a native PHP array representation of the given configuration options.
     *
     * @param array $items
     *
     * @return array
     */
    public static function convertItemsToArray(array $items)
    {
        $result = $items;
        foreach ($items as $key => $value) {
            if (\is_object($value) && \method_exists($value, 'toArray')) {
                $result[$key] = $value->toArray();
            }
        }

        return $result;
    }

      /**
     * Splits a property path to parts
     *
     * @param string $propertyPath
     *
     * @return string[]
     */
    public static function explodePropertyPath($propertyPath)
    {
        return \explode(self::PATH_DELIMITER, $propertyPath);
    }

    /**
     * Makes a deep copy of an array of objects.
     *
     * @param object[] $objects
     *
     * @return object[]
     */
    public static function cloneObjects(array $objects)
    {
        $result = [];
        foreach ($objects as $key => $val) {
            $result[$key] = clone $val;
        }

        return $result;
    }

    /**
     * Makes a deep copy of an array of configuration options.
     *
     * @param array $items
     *
     * @return array
     */
    public static function cloneItems(array $items)
    {
        $result = [];
        foreach ($items as $key => $val) {
            if (\is_object($val)) {
                $val = clone $val;
            }
            $result[$key] = $val;
        }

        return $result;
    }
}
