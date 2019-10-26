<?php

namespace Videni\Bundle\RapidGraphQLBundle\Config\Resource;

class Action
{
    private $action;
    private $grid;
    private $accessControl;
    private $accessControlMessage;
    private $controller;

    private $validationGroups = null;
    private $resourceProvider = null;
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

        if (array_key_exists('validation_groups', $config)) {
            $self->setValidationGroups($config['validation_groups']);
        }
        if (array_key_exists('grid', $config)) {
            $self->setGrid($config['grid']);
        }
        if (array_key_exists('action', $config)) {
            $self->setAction($config['action']);
        }
        if (array_key_exists('form', $config)) {
            $self->setForm($config['form']);
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
            $self->setResourceProvider(Service::fromArray($config['resource_provider']));
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
    public function setResourceProvider(Service $resourceProvider)
    {
        $this->resourceProvider = $resourceProvider;

        return $this;
    }
}
