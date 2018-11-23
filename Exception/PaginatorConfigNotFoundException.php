<?php

declare(strict_types=1);

namespace App\Bundle\RestBundle\Exception;

/**
 * Resource class not found exception.
 */
class PaginatorConfigNotFoundException extends \InvalidArgumentException
{
    /**
     * @param string $code
     */
    public function __construct($code)
    {
        parent::__construct(sprintf('Paginator "%s" does not exist.', $code));
    }
}
