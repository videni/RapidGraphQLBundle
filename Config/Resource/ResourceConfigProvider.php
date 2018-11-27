<?php

namespace App\Bundle\RestBundle\Config\Resource;

use App\Bundle\RestBundle\Exception\ConfigNotFoundException;

class ResourceConfigProvider
{
   /**
     * @var PaginatorConfig[]
     */
    private $resourceConfigs = [];

    private $resourceConfigurations;
    private $resourceConfigLoader;

    public function __construct(
        ResourceConfigLoader $resourceConfigLoader,
        array $resourceConfigurations
    ) {
        $this->resourceConfigLoader = $resourceConfigLoader;
        $this->resourceConfigurations = $resourceConfigurations;
    }

    public function get($resourceClass)
    {
        if (array_key_exists($resourceClass, $this->resourceConfigs)) {
            return  $this->resourceConfigs[$resourceClass];
        }

        if (isset($this->resourceConfigurations['resources'][$resourceClass])) {
            $resourceConfig  = $this->resourceConfigLoader->load($this->resourceConfigurations['resources'][$resourceClass]);

            $this->resourceConfigs[$resourceClass] = $resourceConfig;

            return $resourceConfig;
        }

        throw new ConfigNotFoundException('Resource', $resourceClass);
    }

    public function getAll()
    {
        foreach ($this->resourceConfigurations['resources'] as $resourceClass => $resourceConfiguration) {
            $this->resourceConfigs[$resourceClass] = $this->resourceConfigLoader->load($resourceConfiguration);
        }

        return $this->resourceConfigs;
    }
}
