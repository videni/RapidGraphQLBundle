<?php

namespace App\Bundle\RestBundle\Config\Resource;

class ServiceConfig
{
    private $id;
    private $arguments = [];
    private $method;
    private $class;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     *
     * @return self
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getArguments()
    {
        return $this->arguments;
    }

    /**
     * @param mixed $arguments
     *
     * @return self
     */
    public function setArguments($arguments)
    {
        $this->arguments = $arguments;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @param mixed $method
     *
     * @return self
     */
    public function setMethod($method)
    {
        $this->method = $method;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @param mixed $class
     *
     * @return self
     */
    public function setClass($class)
    {
        $this->class = $class;

        return $this;
    }

    public static function fromArray($config = [])
    {
        $self = new self();

        if (array_key_exists('id', $config)) {
            $self->setId($config['id']);
        }
        if (array_key_exists('method', $config)) {
            $self->setMethod($config['method']);
        }
        if (array_key_exists('arguments', $config)) {
            $self->setArguments($config['arguments']);
        }
        if (array_key_exists('class', $config)) {
            $self->setClass($config['class']);
        }

        return $self;
    }
}
