<?php

namespace Videni\Bundle\RestBundle\Config\Resource;

class Operation
{
    private $actions = [];
    private $resource;

    private $validationGroups = null;
    private $normalizationContext = null;

    /**
     * @return mixed
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * @param mixed $resource
     *
     * @return self
     */
    public function setResource($resource)
    {
        $this->resource = $resource;

        return $this;
    }

    /**
     * Checks whether the configuration of at least one action exists.
     *
     * @return bool
     */
    public function hasActions()
    {
        return !empty($this->actions);
    }

    /**
     * Gets the configuration for all actions.
     *
     * @return Operation[] [action name => config, ...]
     */
    public function getActions()
    {
        return $this->actions;
    }

    /**
     * Checks whether the configuration of the action exists.
     *
     * @param string $actionName
     *
     * @return bool
     */
    public function hasAction($actionName)
    {
        return isset($this->actions[$actionName]);
    }

    /**
     * Gets the configuration of the action.
     *
     * @param string $actionName
     *
     * @return Action|null
     */
    public function getAction($actionName)
    {
        if (!isset($this->actions[$actionName])) {
            return null;
        }

        return $this->actions[$actionName];
    }

     /**
     * Adds the configuration of the action.
     *
     * @param string                 $actionName
     * @param Action|null $action
     *
     * @return Action
     */
    public function addAction($actionName, $action = null)
    {
        if (null === $action) {
            $action = new Action();
        }

        $this->actions[$actionName] = $action;

        return $action;
    }

    /**
     * Removes the configuration of the action.
     *
     * @param string $actionName
     */
    public function removeAction($actionName)
    {
        unset($this->actions[$actionName]);
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
    public function getValidationGroups()
    {
        return $this->validationGroups;
    }

    public function getActionAttribute(string $actionName, string $key, $fallback = false)
    {
        $actionAttribute = null;

        if ($this->hasAction($actionName)) {
            $operation = $this->getAction($actionName);
            if ($operation && $getter = $this->getGetter($operation, $key)) {
                $actionAttribute = $operation->$getter();
            }
        }

        if($actionAttribute instanceof Serialization) {
            $mergedAttribtues = $this->mergeOperationLevelAttributes($key, $actionAttribute);

            if($mergedAttribtues !== false) {
                return Serialization::fromArray($mergedAttribtues);
            }
        };

        //empty array
        if (is_array($actionAttribute) && empty($actionAttribute)) {
            $actionAttribute = null;
        }
        if (null !== $actionAttribute) {
            return $actionAttribute;
        }

        if($fallback) {
            return $this->getOperationLevelAttribute($key);
        }

        return null;
    }

    public static function fromArray(array $config)
    {
        $operationConfig = new self();

        if (array_key_exists('validation_groups', $config)) {
            $operationConfig->setValidationGroups($config['validation_groups']);
        }
        if (array_key_exists('resource', $config)) {
            $operationConfig->setResource($config['resource']);
        }
        if (array_key_exists('actions', $config)) {
            foreach ($config['actions'] as $actionName => $actionConfig) {
                $operationConfig->addAction($actionName, Action::fromArray($actionConfig));
            }
        }

        return $operationConfig;
    }


     /**
     * @param object $config
     * @param string $key
     *
     * @return string|string[]|null
     */
    protected function getGetter($config, $key)
    {
        $setter = 'get' . $this->camelize($key);

        return \method_exists($config, $setter)
            ? $setter
            : null;
    }

    private function mergeOperationLevelAttributes($key, $actionLevelAttribute)
    {
        $operationLevelAttribute  = $this->getOperationLevelAttribute($key);

        if(null !== $actionLevelAttribute) {
            return array_merge(
                $operationLevelAttribute->toArray(),
                array_filter($actionLevelAttribute->toArray(),
                    function($value) {
                        return $value !== null;
                    }
                )
            );
        }

        return false;
    }

    private function getOperationLevelAttribute($key)
    {
        $operationLevelAttribute = null;
        if ($getter = $this->getGetter($this, $key)){
            $operationLevelAttribute = $this->$getter();
        }

        return $operationLevelAttribute;
    }

    /**
     * @param string $string
     *
     * @return string
     */
    protected function camelize($string)
    {
        return strtr(\ucwords(strtr($string, ['_' => ' '])), [' ' => '']);
    }
}
