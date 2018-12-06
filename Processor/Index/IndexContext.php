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
}
