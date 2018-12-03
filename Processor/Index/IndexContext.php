<?php

namespace Videni\Bundle\RestBundle\Processor\Index;

use Videni\Bundle\RestBundle\Processor\Context;
use Videni\Bundle\RestBundle\Config\Paginator\PaginatorConfig;

class IndexContext extends Context
{
    const PAGINATOR_CONFIG = 'paginator_config';

    /**
     * {@inheritdoc}
     */
    public function hasPaginatorConfig()
    {
        return $this->has(self::PAGINATOR_CONFIG);
    }

    /**
     * {@inheritdoc}
     */
    public function getPaginatorConfig()
    {
        if (!$this->has(self::PAGINATOR_CONFIG)) {
            $this->loadPaginatorConfig();
        }

        return $this->get(self::PAGINATOR_CONFIG);
    }

    /**
     * {@inheritdoc}
     */
    public function setPaginatorConfig(?PaginatorConfig $paginatorConfig)
    {
        if ($paginatorConfig) {
            $this->set(self::PAGINATOR_CONFIG, $paginatorConfig);
        } else {
            $this->remove(self::PAGINATOR_CONFIG);
        }
    }

     /**
     * Load paginator config
     */
    protected function loadPaginatorConfig()
    {
        $entityClass = $this->getClassName();
        if (empty($entityClass)) {
            throw new RuntimeException(
                'A class name must be set in the context before a paginator config is loaded.'
            );
        }

        if (!$this->hasResourceConfig()) {
            throw new RuntimeException('Resource metadata is not loaded for current request');
        }

        $resourceConfig = $this->getResourceConfig();

        $operationName = $this->getOperationName();
        if (!$operationName) {
            throw new RuntimeException('Make sure operation name is set for current request');
        }

        try {
            $paginatorKey = $this->resourceConfig->getOperationAttribute($operationName, 'paginator', null, true);
            if (null === $paginatorKey) {
                return;
            }

            $config = $this->paginatorConfigProvider->get($paginatorKey);

            $this->set(self::PAGINATOR_CONFIG, $config);
        } catch (\Exception $e) {
            $this->processLoadedConfig(null);

            throw $e;
        }
    }
}
