<?php

namespace App\Bundle\RestBundle\Config;

class PaginatorConfigProvider
{
   /**
     * @var PaginatorConfig[]
     */
    private $paginatorConfigs = [];

    public function __construct(PaginatorConfigLoader $loader, array $paginatorConfigurations)
    {
        foreach ($paginatorConfigurations as $code => $paginatorConfiguration) {
            $this->paginatorConfigs[$code] = $loader->load($code, $paginatorConfiguration);
        }
    }

    public function get($code)
    {
        if (!array_key_exists($code, $this->paginatorConfigs)) {
            throw new UndefinedGridException($code);
        }

        return clone $this->paginatorConfigs[$code];
    }
}
