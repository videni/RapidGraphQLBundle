<?php

namespace Videni\Bundle\RestBundle\Context;

class ResourceContext
{
    private $className;

    private $operationName = null;

    private $action;

    private $resourceConfig;

    private $requestHeaders;

    /**
     * @return mixed
     */
    public function getClassName()
    {
        return $this->className;
    }

    /**
     * @param mixed $className
     *
     * @return self
     */
    public function setClassName($className)
    {
        $this->className = $className;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getOperationName()
    {
        return $this->operationName;
    }

    /**
     * @param mixed $operationName
     *
     * @return self
     */
    public function setOperationName($operationName)
    {
        $this->operationName = $operationName;

        return $this;
    }

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
    public function getResourceConfig()
    {
        return $this->resourceConfig;
    }

    /**
     * @param mixed $resourceConfig
     *
     * @return self
     */
    public function setResourceConfig($resourceConfig)
    {
        $this->resourceConfig = $resourceConfig;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getRequestHeaders()
    {
        return $this->requestHeaders;
    }

    /**
     * @param mixed $requestHeaders
     *
     * @return self
     */
    public function setRequestHeaders($requestHeaders)
    {
        $this->requestHeaders = $requestHeaders;

        return $this;
    }

    public function getOperationConfig()
    {
        $this->assure();

        return $this->resourceConfig->hasOperation($this->operationName) ? $this->resourceConfig->getOperation($this->operationName) : null;
    }

    public function getGrid()
    {
        $this->assure();

        $gridName = $this->resourceConfig->getOperation($this->operationName)->getGrid();
        if (!$gridName || !$this->resourceConfig->hasGrid($gridName)) {
            return null;
        }

        return $this->resourceConfig->getGrid($gridName);
    }

    private function assure()
    {
        if (null === $this->operationName) {
            throw new \RuntimeException('Operation must be set in the context before operation config is requested');
        }

        if (null === $this->resourceConfig) {
            throw new \RuntimeException('Resource must be set in the context before operation config is requested');
        }
    }
}
