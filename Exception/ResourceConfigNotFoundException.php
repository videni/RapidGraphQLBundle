<?php

declare(strict_types=1);

namespace App\Bundle\RestBundle\Exception;

/**
 * Resource class not found exception.
 */
class ResourceConfigNotFoundException extends \InvalidArgumentException
{
    /**
     * @param string $code
     */
    public function __construct($code)
    {
        parent::__construct(sprintf('Resource configuration for "%s" does not exist.', $code));
    }
}
