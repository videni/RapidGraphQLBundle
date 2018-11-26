<?php

namespace App\Bundle\RestBundle\Config\Paginator;

/**
 * Represents the configuration of a field that can be used to filter data.
 */
class FilterConfig
{
    private $type;
    private $allowArray = false;
    private $allowRange = false;
    private $description;
    private $filterOptions;
    private $propertyPath;
    private $filterOperators;

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     *
     * @return self
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getAllowArray()
    {
        return $this->allowArray;
    }

    /**
     * @param mixed $allowArray
     *
     * @return self
     */
    public function setAllowArray($allowArray)
    {
        $this->allowArray = $allowArray;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getAllowRange()
    {
        return $this->allowRange;
    }

    /**
     * @param mixed $allowRange
     *
     * @return self
     */
    public function setAllowRange($allowRange)
    {
        $this->allowRange = $allowRange;

        return $this;
    }

    public function isRangeAllowed()
    {
        return $this->allowRange;
    }

    public function isArrayAllowed()
    {
        return $this->allowArray;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $description
     *
     * @return self
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getOptions()
    {
        return $this->filterOptions;
    }

    /**
     * @param mixed $filterOptions
     *
     * @return self
     */
    public function setOptions($filterOptions)
    {
        $this->filterOptions = $filterOptions;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPropertyPath()
    {
        return $this->propertyPath;
    }

    /**
     * @param mixed $propertyPath
     *
     * @return self
     */
    public function setPropertyPath($propertyPath)
    {
        $this->propertyPath = $propertyPath;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getOperaters()
    {
        return $this->filterOperators;
    }

    /**
     * @param mixed $filterOperators
     *
     * @return self
     */
    public function setOperaters($filterOperators)
    {
        $this->filterOperators = $filterOperators;

        return $this;
    }

    public static function fromArray($config)
    {
        $self = new self();

        if (array_key_exists('type', $config)) {
            $self->setType($config['type']);
        }
        if (array_key_exists('description', $config)) {
            $self->setDescription($config['description']);
        }
        if (array_key_exists('allow_array', $config)) {
            $self->setAllowArray($config['allow_array']);
        }
        if (array_key_exists('allow_range', $config)) {
            $self->setAllowRange($config['allow_range']);
        }
        if (array_key_exists('property_path', $config)) {
            $self->setPropertyPath($config['property_path']);
        }
        if (array_key_exists('collection', $config)) {
            $self->setCollection($config['collection']);
        }
        if (array_key_exists('position', $config)) {
            $self->setPosition($config['position']);
        }
        if (array_key_exists('options', $config)) {
            $self->setOptions($config['options']);
        }
        if (array_key_exists('operators', $config)) {
            $self->setOperaters($config['operators']);
        }

        return $self;
    }
}
