<?php

namespace Videni\Bundle\RestBundle\Config\Resource;

use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\PropertyAccess\PropertyPathInterface;
use Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException;

/**
 * An object oriented way to manipulate JMS serialization hierarchy groups
 */
class SerializationGroup implements \ArrayAccess, \IteratorAggregate
{
    /**
     *  complex example can be this:
     *  [
     *      'Default',
     *      'items' => [
     *          'User',
     *          'Report',
     *          'auto' => [
     *              'CarModel'
     *          ]
     *      ]
     *  ]
     * both the 'items' and 'auto` are class properties, the `items` attribute is in  group `Default`,  the `auto` atrribute is in group `Report`.  this features is provided by JMS serializer.  check https://jmsyst.com/libs/serializer/master/cookbook/exclusion_strategies#overriding-groups-of-deeper-branches-of-the-graph for more details
     *
     * @var array
     */
    private $params = [];

     /** @var PropertyAccessorInterface */
    protected $accessor;

    public function __construct(array $params = [])
    {
        $this->params = $params;
    }

    public function toArray()
    {
        return $this->params;
    }

    /**
     * {@inheritDoc}
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->params);
    }

    /**
     * {@inheritDoc}
     */
    public function offsetExists($offset)
    {
        return isset($this->params[$offset]);
    }

    /**
     * {@inheritDoc}
     */
    public function offsetGet($offset)
    {
        return $this->params[$offset];
    }

    /**
     * {@inheritDoc}
     */
    public function offsetSet($offset, $value)
    {
        $this->params[$offset] = $value;

        return $this;
    }

     /**
     * {@inheritDoc}
     */
    public function offsetUnset($offset)
    {
        unset($this->params[$group]);

        return $this;
    }

     /**
     * Try to get property using PropertyAccessor
     *
     * @param string|PropertyPathInterface $path
     * @param null                         $default
     *
     * @return mixed
     */
    public function offsetGetByPath($path, $default = null)
    {
        try {
            $value = $this->getPropertyAccessor()->getValue($this, $path);
        } catch (NoSuchPropertyException $e) {
            return $default;
        }

        return null !== $value ? $value : $default;
    }

     /**
     * Check property existence using PropertyAccessor
     *
     * @param string|PropertyPathInterface $path
     *
     * @return mixed
     */
    public function offsetExistByPath($path)
    {
        try {
            $value = $this->getPropertyAccessor()->getValue($this, $path);
        } catch (NoSuchPropertyException $e) {
            return false;
        }

        // If NULL then result is FALSE, same behavior as function isset() has
        return $value !== null;
    }

    /**
     *  Remove group using PropertyAccessor
     *
     * @param string|PropertyPathInterface $path
     *
     * @return $this
     */
    public function offsetUnsetByPath($path)
    {
         $this->offsetSetByPath($path, null);

        $parts = $this->explodeArrayPath($path);
        if (count($parts) > 1) {
            // extract last part
            $lastPart = $parts[count($parts) - 1];
            unset($parts[count($parts) - 1]);
            $previousPath = $this->implodeArrayPath($parts);

            // rewrite data
            $previousValue = $this->getPropertyAccessor()->getValue($this, $previousPath);
            if ($previousValue && is_array($previousValue) && array_key_exists($lastPart, $previousValue)) {
                unset($previousValue[$lastPart]);
                $this->offsetSetByPath($previousPath, $previousValue);
            }
        } else {
            $this->offsetUnset($parts[0]);
        }

        return $this;
    }

    /**
     * Set property using PropertyAccessor
     *
     * @param string|PropertyPathInterface $path
     * @param mixed                        $value
     *
     * @return $this
     */
    public function offsetSetByPath($path, $value)
    {
        $this->getPropertyAccessor()->setValue($this->params, $path, $value);

        return $this;
    }

    private function getPropertyAccessor()
    {
        if ($this->accessor) {
            return $this->accessor;
        }
        $this->accessor = new PropertyAccessor();

        return $this->accessor;
    }

    /**
     * @param string|PropertyPathInterface $path
     * @return array
     */
    protected function explodeArrayPath($path)
    {
        return explode('.', strtr($path, [
                '][' => '.',
                '[' => '',
                ']' => ''
            ])
        );
    }

    /**
     * @param array $parts
     * @return string
     */
    protected function implodeArrayPath(array $parts)
    {
        return '[' . implode('][', $parts) . ']';
    }
}
