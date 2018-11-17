<?php

declare(strict_types=1);

namespace App\Bundle\RestBundle\Utils;

use App\Bundle\RestBundle\Operation\OperationType;

/**
 * Extracts data used by the library form given attributes.
 *
 * @internal
 */
final class AttributesExtractor
{
    private function __construct()
    {
    }

    /**
     * Extracts resource class, operation name and format request attributes. Returns an empty array if the request does
     * not contain required attributes.
     */
    public static function extractAttributes(array $attributes): array
    {
        $result = ['resource_class' => $attributes['_api_resource_class'] ?? null];
        if (null === $result['resource_class']) {
            return [];
        }

        $hasRequestAttributeKey = false;
        foreach (OperationType::TYPES as $operationType) {
            $attribute = "_api_{$operationType}_operation_name";
            if (isset($attributes[$attribute])) {
                $result["{$operationType}_operation_name"] = $attributes[$attribute];
                $hasRequestAttributeKey = true;
                break;
            }
        }

        if (false === $hasRequestAttributeKey) {
            return [];
        }

        $result += [
            'receive' => (bool) ($attributes['_api_receive'] ?? true),
            'persist' => (bool) ($attributes['_api_persist'] ?? true),
        ];

        return $result;
    }
}
