<?php

declare(strict_types=1);

namespace Videni\Bundle\RestBundle\Provider;

use Videni\Bundle\RestBundle\Exception\InvalidArgumentException;
use Videni\Bundle\RestBundle\Config\Resource\Operation;

/**
 * {@inheritdoc}
 */
final class FormatsProvider implements FormatsProviderInterface
{
    private $configuredFormats;

    public function __construct(array $configuredFormats)
    {
        $this->configuredFormats = $configuredFormats;
    }

    /**
     * {@inheritdoc}
     *
     * @throws InvalidArgumentException
     */
    public function getFormats(Operation $opeartionConfig, string $actionName, $operationName): array
    {
        if (!$formats = $opeartionConfig->getActionAttribute($actionName, 'formats')) {
            return $this->configuredFormats;
        }

        if (!\is_array($formats)) {
            throw new InvalidArgumentException(sprintf("The 'formats' attributes must be an array, %s given for action '%s' of opeartion %s .", \gettype($formats), $actionName, $operationName));
        }

        return $this->getOperationFormats($formats);
    }

    /**
     * Filter and populate the acceptable formats.
     *
     * @throws InvalidArgumentException
     */
    private function getOperationFormats(array $annotationFormats): array
    {
        $resourceFormats = [];
        foreach ($annotationFormats as $format => $value) {
            if (!is_numeric($format)) {
                $resourceFormats[$format] = (array) $value;
                continue;
            }
            if (!\is_string($value)) {
                throw new InvalidArgumentException(sprintf("The 'formats' attributes value must be a string when trying to include an already configured format, %s given.", \gettype($value)));
            }
            if (array_key_exists($value, $this->configuredFormats)) {
                $resourceFormats[$value] = $this->configuredFormats[$value];
                continue;
            }

            throw new InvalidArgumentException(sprintf("You either need to add the format '%s' to your project configuration or declare a mime type for it in your annotation.", $value));
        }

        return $resourceFormats;
    }
}
