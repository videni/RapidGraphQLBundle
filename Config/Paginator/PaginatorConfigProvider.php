<?php

namespace App\Bundle\RestBundle\Config\Paginator;

use App\Bundle\RestBundle\Exception\ConfigNotFoundException;

class PaginatorConfigProvider
{
   /**
     * @var PaginatorConfig[]
     */
    private $paginatorConfigs = [];

    private $paginatorConfigurations;
    private $paginatorConfigLoader;

    public function __construct(
        PaginatorConfigLoader $paginatorConfigLoader,
        array $paginatorConfigurations
    ) {
        $this->paginatorConfigLoader = $paginatorConfigLoader;
        $this->paginatorConfigurations = $paginatorConfigurations;
    }

    public function get($paginatorClass)
    {
        if (array_key_exists($paginatorClass, $this->paginatorConfigs)) {
            return  $this->paginatorConfigs[$paginatorClass];
        }

        if (isset($this->paginatorConfigurations['paginators'][$paginatorClass])) {
            $paginatorConfig  = $this->paginatorConfigLoader->load($this->paginatorConfigurations['paginators'][$paginatorClass]);

            $this->paginatorConfigs[$paginatorClass] = $paginatorConfig;

            return $paginatorConfig;
        }

        throw new ConfigNotFoundException('Paginator', $paginatorClass);
    }

    public function getAll()
    {
        foreach ($this->paginatorConfigurations['paginators'] as $paginatorClass => $paginatorConfiguration) {
            $this->paginatorConfigs[$paginatorClass] = $this->paginatorConfigLoader->load($paginatorConfiguration);
        }

        return $this->paginatorConfigs;
    }
}
