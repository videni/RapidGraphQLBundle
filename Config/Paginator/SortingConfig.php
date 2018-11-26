<?php

namespace App\Bundle\RestBundle\Config\Paginator;

/**
 * Represents the configuration of a field that can be used to filter data.
 */
class SortConfig
{
    private $propertyPath;
    private $description;
    private $order;

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
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @param mixed $order
     *
     * @return self
     */
    public function setOrder($order)
    {
        $this->order = $order;

        return $this;
    }

    public static function fromArray(array $config)
    {
        $self = new self();
        if (array_key_exists('description', $config)) {
            $self->setDescription($config['description']);
        }
        if (array_key_exists('order', $config)) {
            $self->setOrder($config['order']);
        }
        if (array_key_exists('property_path', $config)) {
            $self->setPropertyPath($config['property_path']);
        }
    }
}
