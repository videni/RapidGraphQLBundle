<?php

namespace Videni\Bundle\RestBundle\Config\Resource;

class Operation
{
    private $action;
    private $defaults;
    private $requirements;
    private $path;
    private $grid;
    private $methods;
    private $routeName;
    private $resourceProvider;
    private $accessControl;
    private $accessControlMessage;
    private $aclEnabled = false;
    private $controller;

    private $formats = null;
    private $factory = null;
    private $repository = null;
    private $normalizationContext = null;
    private $denormalizationContext = null;
    private $validationGroups = null;
    private $form = null;

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

    public function setFactory(?Service $factory)
    {
        $this->factory = $factory;
        return $this;
    }

    public function getFactory(): ?Service
    {
        return $this->factory;
    }

    public function setRepository(?Service $repostory)
    {
        $this->repository = $repostory;

        return $this;
    }

    public function getRepository(): ?Service
    {
        return $this->repository;
    }

    public function setNormalizationContext(?Serialization $normalizationContext)
    {
        $this->normalizationContext = $normalizationContext;

        return $this;
    }

    public function getNormalizationContext(): ?Serialization
    {
        return $this->normalizationContext;
    }

    public function setDenormalizationContext(?Serialization $denormalizationContext)
    {
        $this->denormalizationContext = $denormalizationContext;

        return $this;
    }

    public function getDenormalizationContext(): ?Serialization
    {
        return $this->denormalizationContext;
    }

    /**
     * @return mixed
     */
    public function getAccessControl()
    {
        return $this->accessControl;
    }

    /**
     * @param mixed $accessControl
     *
     * @return self
     */
    public function setAccessControl($accessControl)
    {
        $this->accessControl = $accessControl;

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
            $self->setDenormalizationContext(Serialization::fromArray($config['denormalization_context']));
        }
        if (array_key_exists('normalization_context', $config)) {
            $self->setNormalizationContext(Serialization::fromArray($config['normalization_context']));
        }
        if (array_key_exists('validation_groups', $config)) {
            $self->setValidationGroups($config['validation_groups']);
        }
        if (array_key_exists('formats', $config)) {
            $self->setFormats($config['formats']);
        }
        if (array_key_exists('grid', $config)) {
            $self->setGrid($config['grid']);
        }
        if (array_key_exists('factory', $config)) {
            $self->setFactory(Service::fromArray($config['factory']));
        }
        if (array_key_exists('repository', $config)) {
            $self->setRepository(Service::fromArray($config['repository']));
        }

        if (array_key_exists('action', $config)) {
            $self->setAction($config['action']);
        }
        if (array_key_exists('defaults', $config)) {
            $self->setDefaults($config['defaults']);
        }
        if (array_key_exists('requirements', $config)) {
            $self->setRequirements($config['requirements']);
        }
        if (array_key_exists('path', $config)) {
            $self->setPath($config['path']);
        }
        if (array_key_exists('form', $config)) {
            $self->setForm($config['form']);
        }
        if (array_key_exists('methods', $config)) {
            $self->setMethods($config['methods']);
        }
        if (array_key_exists('access_control_message', $config)) {
            $self->setAccessControlMessage($config['access_control_message']);
        }
        if (array_key_exists('access_control', $config)) {
            $self->setAccessControl($config['access_control']);
        }
        if (array_key_exists('controller', $config)) {
            $self->setController($config['controller']);
        }
        if (array_key_exists('resource_provider', $config)) {
            $self->setResourceProvider($config['resource_provider']);
        }
        if (array_key_exists('route_name', $config)) {
            $self->setRouteName($config['route_name']);
        }
        if (array_key_exists('acl_enabled', $config)) {
            $self->setAclEnabled($config['acl_enabled']);
        }

        return $self;
    }

    /**
     * @return mixed
     */
    public function getGrid()
    {
        return $this->grid;
    }

    /**
     * @param mixed $grid
     *
     * @return self
     */
    public function setGrid($grid)
    {
        $this->grid = $grid;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getForm()
    {
        return $this->form;
    }

    /**
     * @param mixed $form
     *
     * @return self
     */
    public function setForm($form)
    {
        $this->form = $form;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getResourceProvider()
    {
        return $this->resourceProvider;
    }

    /**
     * @param mixed $resourceProvider
     *
     * @return self
     */
    public function setResourceProvider($resourceProvider)
    {
        $this->resourceProvider = $resourceProvider;

        return $this;
    }

    /**
     * @return mixed
     */
    public function isAclEnabled()
    {
        return $this->aclEnabled;
    }

    /**
     * @param mixed $aclEnabled
     *
     * @return self
     */
    public function setAclEnabled($aclEnabled)
    {
        $this->aclEnabled = $aclEnabled;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getRequirements()
    {
        return $this->requirements;
    }

    /**
     * @param mixed $requirements
     *
     * @return self
     */
    public function setRequirements($requirements)
    {
        $this->requirements = $requirements;

        return $this;
    }
}
