<?php

namespace Videni\Bundle\RestBundle\Config\Resource;

class OperationConfig
{
    private $action;
    private $defaults = [];
    private $path;
    private $paginator = null;
    private $methods =  [];
    private $routeName;
    private $controller;
    private $accessControll;
    private $accessControlMessage;

    private $factory;
    private $repository;
    private $normalizationContext;
    private $denormalizationContext;
    private $validationGroups;
    private $formats = [];

    /**
     * @return mixed
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @param mixed $action
     *
     * @return self
     */
    public function setAction($action)
    {
        $this->action = $action;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDefaults()
    {
        return $this->defaults;
    }

    /**
     * @param mixed $defaults
     *
     * @return self
     */
    public function setDefaults($defaults)
    {
        $this->defaults = $defaults;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param mixed $path
     *
     * @return self
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getMethods()
    {
        return $this->methods;
    }

    /**
     * @param mixed $methods
     *
     * @return self
     */
    public function setMethods($methods)
    {
        $this->methods = $methods;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getRouteName()
    {
        return $this->routeName;
    }

    /**
     * @param mixed $routeName
     *
     * @return self
     */
    public function setRouteName($routeName)
    {
        $this->routeName = $routeName;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getController()
    {
        return $this->controller;
    }

    /**
     * @param mixed $controller
     *
     * @return self
     */
    public function setController($controller)
    {
        $this->controller = $controller;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getValidationGroups()
    {
        return $this->validationGroups;
    }

    /**
     * @param mixed $validationGroups
     *
     * @return self
     */
    public function setValidationGroups($validationGroups)
    {
        $this->validationGroups = $validationGroups;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getFormats()
    {
        return $this->formats;
    }

    /**
     * @param mixed $formats
     *
     * @return self
     */
    public function setFormats($formats)
    {
        $this->formats = $formats;

        return $this;
    }

    public function setFactory(?ServiceConfig $factory)
    {
        $this->factory = $factory;
        return $this;
    }

    public function getFactory(): ?ServiceConfig
    {
        return $this->factory;
    }

    public function setRepository(?ServiceConfig $repostory)
    {
        $this->repository = $repostory;

        return $this;
    }

    public function getRepository(): ?ServiceConfig
    {
        return $this->repository;
    }

    public function setNormalizationContext(?SerializationConfig $normalizationContext)
    {
        $this->normalizationContext = $normalizationContext;

        return $this;
    }

    public function getNormalizationContext(): ?SerializationConfig
    {
        return $this->normalizationContext;
    }

    public function setDenormalizationContext(?SerializationConfig $denormalizationContext)
    {
        $this->denormalizationContext = $denormalizationContext;

        return $this;
    }

    public function getDenormalizationContext(): ?SerializationConfig
    {
        return $this->denormalizationContext;
    }

    /**
     * @return mixed
     */
    public function getAccessControll()
    {
        return $this->accessControll;
    }

    /**
     * @param mixed $accessControll
     *
     * @return self
     */
    public function setAccessControll($accessControll)
    {
        $this->accessControll = $accessControll;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getAccessControlMessage()
    {
        return $this->accessControlMessage;
    }

    /**
     * @param mixed $accessControlMessage
     *
     * @return self
     */
    public function setAccessControlMessage($accessControlMessage)
    {
        $this->accessControlMessage = $accessControlMessage;

        return $this;
    }

    public static function fromArray($config)
    {
        $self = new self();

        if (array_key_exists('denormalization_context', $config)) {
            $self->setDenormalizationContext(SerializationConfig::fromArray($config['denormalization_context']));
        }
        if (array_key_exists('normalization_context', $config)) {
            $self->setDenormalizationContext(SerializationConfig::fromArray($config['normalization_context']));
        }
        if (array_key_exists('validation_groups', $config)) {
            $self->setValidationGroups($config['validation_groups']);
        }
        if (array_key_exists('formats', $config)) {
            $self->setFormats($config['formats']);
        }
        if (array_key_exists('paginator', $config)) {
            $self->setPaginator($config['paginator']);
        }
        if (array_key_exists('factory', $config)) {
            $self->setFactory(ServiceConfig::fromArray($config['factory']));
        }
        if (array_key_exists('repository', $config)) {
            $self->setRepository(ServiceConfig::fromArray($config['repository']));
        }

        if (array_key_exists('action', $config)) {
            $self->setAction($config['action']);
        }
        if (array_key_exists('defaults', $config)) {
            $self->setDefaults($config['defaults']);
        }
        if (array_key_exists('path', $config)) {
            $self->setPath($config['path']);
        }
        if (array_key_exists('methods', $config)) {
            $self->setMethods($config['methods']);
        }
        if (array_key_exists('access_control_message', $config)) {
            $self->setAccessControlMessage($config['access_control_message']);
        }
        if (array_key_exists('access_controll', $config)) {
            $self->setAccessControll($config['access_controll']);
        }
        if (array_key_exists('controller', $config)) {
            $self->setController($config['controller']);
        }
        if (array_key_exists('route_name', $config)) {
            $self->setRouteName($config['route_name']);
        }

        return $self;
    }

    /**
     * @return mixed
     */
    public function getPaginator()
    {
        return $this->paginator;
    }

    /**
     * @param mixed $paginator
     *
     * @return self
     */
    public function setPaginator($paginator)
    {
        $this->paginator = $paginator;

        return $this;
    }
}
