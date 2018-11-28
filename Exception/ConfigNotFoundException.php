<?php

declare(strict_types=1);

namespace Videni\Bundle\RestBundle\Exception;

/**
 * Resource class not found exception.
 */
class ConfigNotFoundException extends \InvalidArgumentException
{
    /**
     * @param string $code
     */
    public function __construct($section, $class)
    {
        parent::__construct(sprintf('%s configuration for "%s" does not exist.', $section, $class));
    }
}
