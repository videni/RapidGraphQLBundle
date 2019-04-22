<?php

namespace Videni\Bundle\RestBundle\Context;

use Videni\Bundle\RestBundle\Config\Resource\Action;
use Videni\Bundle\RestBundle\Config\Resource\Operation;
use Videni\Bundle\RestBundle\Config\Resource\Resource;

class ResourceContext
{
    /** @var string */
    private $actionName;

    /** @var string */
    private $operationName;

    /**
     * @var Operation
     */
    private $action;

    /**
     * @var Operation
     */
    private $operation;

    /**
     * @var Resource
     */
    private $resource;

    public function __construct($operationName, Operation $operation, $actionName, Action $action, Resource $resource)
    {
        $this->operationName = $operationName;
        $this->operation = $operation;
        $this->actionName = $actionName;
        $this->action = $action;
        $this->resource = $resource;
    }

    /**
     * @return mixed
     */
    public function getActionName()
    {
        return $this->actionName;
    }

    /**
     * @return mixed
     */
    public function getOperationName()
    {
        return $this->operationName;
    }

    /**
     * @return mixed
     */
    public function getAction()
    {
        return $this->action;
    }

    public function getActionType()
    {
        return $this->getAction()->getAction();
    }

    /**
     * @return mixed
     */
    public function getOperation()
    {
        return $this->operation;
    }

    public function getGrid()
    {
        return $this->action->getGrid();
    }

    /**
     * @return mixed
     */
    public function getResource()
    {
        return $this->resource;
    }
}
