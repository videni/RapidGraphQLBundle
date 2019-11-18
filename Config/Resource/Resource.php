<?php

namespace Videni\Bundle\RapidGraphQLBundle\Config\Resource;

class Resource
{
    private $scope;
    private $shortName;
    private $description;
    private $entityClass;
    private $factoryClass = null;
    private $repositoryClass = null;
    private $repositoryAutoAlias = true;
    private $factoryAutoAlias = true;

    private $form = [];

    public static function fromArray($config)
    {
        $self = new self();

        $self = new Resource();

        if (array_key_exists('repository_class', $config)) {
            $self->setRepositoryClass($config['repository_class']);
        }
        if (array_key_exists('entity_class', $config)) {
            $self->setEntityClass($config['entity_class']);
        }
        if (array_key_exists('short_name', $config)) {
            $self->setShortName($config['short_name']);
        }
        if (array_key_exists('scope', $config)) {
            $self->setScope($config['scope']);
        }
        if (array_key_exists('form', $config)) {
            if(isset($config['form']['class'])) {
                $self->setFormClass($config['form']['class']) ;
            }
            if(isset($config['form']['validation_groups'])) {
                $self->setFormValidationGroups($config['form']['validation_groups']);
            }
            if(isset($config['form']['handler'])) {
                $self->setFormHandler($config['form']['handler']);
            }
        }
        if (array_key_exists('repository', $config)) {
            if(isset($config['repository']['class'])) {
                $self->setRepositoryClass($config['repository']['class']) ;
            }
            if(isset($config['repository']['auto_alias'])) {
                $self->setRepositoryAutoAlias($config['repository']['auto_alias']);
            }
        }
        if (array_key_exists('factory', $config)) {
            if(isset($config['factory']['class'])) {
                $self->setFactoryClass($config['factory']['class']) ;
            }
            if(isset($config['factory']['auto_alias'])) {
                $self->setFactoryAutoAlias($config['factory']['auto_alias']);
            }
        }

        return $self;
    }

    /**
     * @return mixed
     */
    public function getShortName()
    {
        return $this->shortName;
    }

    /**
     * @param mixed $shortName
     *
     * @return self
     */
    public function setShortName($shortName)
    {
        $this->shortName = $shortName;

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

    public function setNormalizationContext(?Serialization $normalizationContext)
    {
        $this->normalizationContext = $normalizationContext;

        return $this;
    }

    public function getNormalizationContext(): ?Serialization
    {
        return $this->normalizationContext;
    }

    /**
     * @return mixed
     */
    public function getFormClass()
    {
        return  isset($this->form['class'])?  $this->form['class']: null;
    }

    /**
     * @param mixed $form
     *
     * @return self
     */
    public function setFormClass($form)
    {
        $this->form['class'] = $form;

        return $this;
    }

     /**
     * @param mixed $validationGroups
     *
     * @return self
     */
    public function setFormValidationGroups($validationGroups)
    {
        $this->form['validation_groups'] = $validationGroups;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getFormValidationGroups()
    {
        return isset($this->form['validation_groups']) ? $this->form['validation_groups']: null;
    }

     /**
     * @param mixed $validationGroups
     *
     * @return self
     */
    public function setFormHandler($onSuccess)
    {
        $this->form['handler'] = $onSuccess;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getFormHandler()
    {
        return isset($this->form['handler']) ? $this->form['handler']: null;
    }

    /**
     * @return mixed
     */
    public function getRepositoryClass()
    {
        return $this->repositoryClass;
    }

    /**
     * @param mixed $repositoryClass
     *
     * @return self
     */
    public function setRepositoryClass($repositoryClass)
    {
        $this->repositoryClass = $repositoryClass;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getFactoryClass()
    {
        return $this->factoryClass;
    }

    /**
     * @param mixed $factoryClass
     *
     * @return self
     */
    public function setFactoryClass($factoryClass)
    {
        $this->factoryClass = $factoryClass;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getScope()
    {
        return $this->scope;
    }

    /**
     * @param mixed $scope
     *
     * @return self
     */
    public function setScope($scope)
    {
        $this->scope = $scope;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getEntityClass()
    {
        return $this->entityClass;
    }

    /**
     * @param mixed $entityClass
     *
     * @return self
     */
    public function setEntityClass($entityClass)
    {
        $this->entityClass = $entityClass;

        return $this;
    }

    public function getRepositoryAutoAlias()
    {
        return $this->repositoryAutoAlias;
    }

    /**
     *
     * @return  self
     */
    public function setRepositoryAutoAlias($repositoryAutoAlias)
    {
        $this->repositoryAutoAlias = $repositoryAutoAlias;

        return $this;
    }

    public function getFactoryAutoAlias()
    {
        return $this->factoryAutoAlias;
    }

    /**
     * @return  self
     */
    public function setFactoryAutoAlias($factoryAutoAlias)
    {
        $this->factoryAutoAlias = $factoryAutoAlias;

        return $this;
    }
}
