<?php

namespace App\Bundle\RestBundle\Config;

use App\Bundle\RestBundle\Exception\PaginatorConfigNotFoundException;

class PaginatorConfigProvider
{
   /**
     * @var PaginatorConfig[]
     */
    private $paginatorConfigs = [];

    private $paginatorConfigurations;

    private $loader;


    public function __construct(PaginatorConfigLoader $loader, array $paginatorConfigurations)
    {
        $this->loader = $loader;
        $this->paginatorConfigurations = $paginatorConfigurations;
    }

    public function get($code)
    {
        if (array_key_exists($code, $this->paginatorConfigs)) {
            return  $this->paginatorConfigs[$code];
        }

        if (isset($this->$paginatorConfigurations[$code])) {
            $this->paginatorConfigs[$code] = $loader->load($code, $this->$paginatorConfigurations[$code]);
        }

        throw new PaginatorConfigNotFoundException($code);
    }
}
