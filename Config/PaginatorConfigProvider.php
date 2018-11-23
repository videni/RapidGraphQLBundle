<?php

namespace App\Bundle\RestBundle\Config;

class PaginatorConfigProvider
{
   /**
     * @var PaginatorConfig[]
     */
    private $paginatorConfigs = [];

    private $paginatorConfiguration;

    private $loader;


    public function __construct(PaginatorConfigLoader $loader, array $paginatorConfigurations)
    {
        $this->loader = $loader;
        $this->paginatorConfiguration = $paginatorConfiguration;
    }

    public function get($code)
    {
        if (array_key_exists($code, $this->paginatorConfigs)) {
            return  $this->paginatorConfigs[$code];
        }

        if (isset($this->paginatorConfiguration[$code])) {
            $this->paginatorConfigs[$code] = $loader->load($code, $paginatorConfiguration);
        }

        throw new UndefinedGridException($code);
    }
}
