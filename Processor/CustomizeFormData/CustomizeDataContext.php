<?php

namespace Videni\Bundle\RestBundle\Processor\CustomizeFormData;

use Videni\Bundle\RestBundle\Config\Resource\ResourceConfig;
use Oro\Component\ChainProcessor\Context;

/**
 * The base execution context for processors for "customize_loaded_data" and "customize_form_data" actions.
 */
abstract class CustomizeDataContext extends Context
{
    /** FQCN of a root entity */
    const ROOT_CLASS_NAME = 'rootClass';

    /** a path inside a root entity to a customizing entity */
    const PROPERTY_PATH = 'propertyPath';

    /** FQCN of a customizing entity */
    const CLASS_NAME = 'class';

    /** API version */
    const VERSION = 'version';

    /** @var ResourceConfig|null */
    private $rootConfig;

    /** @var ResourceConfig|null */
    private $config;

  /**
     * Gets API version
     *
     * @return string
     */
    public function getVersion()
    {
        return $this->get(self::VERSION);
    }

    /**
     * Sets API version
     *
     * @param string $version
     */
    public function setVersion($version)
    {
        $this->set(self::VERSION, $version);
    }

    /**
     * Gets FQCN of a root entity.
     *
     * @return string|null
     */
    public function getRootClassName()
    {
        return $this->get(self::ROOT_CLASS_NAME);
    }

    /**
     * Sets FQCN of a root entity.
     *
     * @param string $className
     */
    public function setRootClassName($className)
    {
        $this->set(self::ROOT_CLASS_NAME, $className);
    }

    /**
     * Gets a path inside a root entity to a customizing entity.
     *
     * @return string|null
     */
    public function getPropertyPath()
    {
        return $this->get(self::PROPERTY_PATH);
    }

    /**
     * Sets a path inside a root entity to a customizing entity.
     *
     * @param string $propertyPath
     */
    public function setPropertyPath($propertyPath)
    {
        $this->set(self::PROPERTY_PATH, $propertyPath);
    }

    /**
     * Gets FQCN of a customizing entity.
     *
     * @return string
     */
    public function getClassName()
    {
        return $this->get(self::CLASS_NAME);
    }

    /**
     * Sets FQCN of a customizing entity.
     *
     * @param string $className
     */
    public function setClassName($className)
    {
        $this->set(self::CLASS_NAME, $className);
    }

    /**
     * Gets a configuration of a root entity.
     *
     * @return ResourceConfig|null
     */
    public function getRootConfig()
    {
        return $this->rootConfig;
    }

    /**
     * Sets a configuration of a root entity.
     *
     * @param ResourceConfig|null $config
     */
    public function setRootConfig(ResourceConfig $config = null)
    {
        $this->rootConfig = $config;
    }

    /**
     * Gets a configuration of a customizing entity.
     *
     * @return ResourceConfig|null
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Sets a configuration of a customizing entity.
     *
     * @param ResourceConfig|null $config
     */
    public function setConfig(ResourceConfig $config = null)
    {
        $this->config = $config;
    }
}
