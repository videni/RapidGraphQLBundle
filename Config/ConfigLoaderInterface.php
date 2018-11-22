<?php

namespace App\Bundle\RestBundle\Config;

/**
 * An interface for configuration section loaders.
 */
interface ConfigLoaderInterface
{
    /**
     * Loads a configuration from an array.
     *
     * @param array $config
     *
     * @return mixed
     */
    public function load(array $config);
}
