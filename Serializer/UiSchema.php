<?php

namespace Videni\Bundle\RapidGraphQLBundle\Serializer;

class UiSchema {
    /**
     *  Extract ui schema from form schema, this will mutate the formSchema parameter.
     *  for example:
     * {
     *    "title": "product",
     *    "type": "object",
     *    "properties": {
     *        "name": {
     *            "type": "string",
     *            "title": "Name",
     *            "propertyOrder": 1,
     *            "ui": {
     *                "disabled": true
     *            }
     *        }
     *    }
     * }
     *
     *  the ui key will be removed
     *
     * @param  array  &$formSchema
     *
     * @return array
     */
    public static function extract(array &$formSchema) {
        list($type) = is_array($formSchema['type']) ? $formSchema['type']: [$formSchema['type']];
        if ('object' === $type) {
            return self::extractObject($formSchema);
        }

        if ('array' === $type && isset($formSchema['items'])) {
           return self::extractArray($formSchema);
        }

        if (in_array($type, ['number', 'boolean', 'integer', 'number', 'string'])) {
            return self::extractUiSchema($formSchema);
        }

        throw new \Exception(sprintf(
            'JSON Schema type %s is not supported, available are %s',
            $type,
            implode(',', [ 'object', 'boolean', 'integer', 'number', 'object', 'array', 'string'])
        ));
    }

    protected static function extractObject(array &$formSchema)
    {
        $uiSchema = [];
        if (isset($formSchema['properties'])) {
            $properties = &$formSchema['properties'];
            $propertyOrders = [];

            foreach($properties as $propertyName => &$property) {
                if(isset($property['propertyOrder'])) {
                    $propertyOrders[$propertyName] = $property['propertyOrder'];
                    unset($property['propertyOrder']);
                }
                $data = self::extract($property);
                if (!empty($data)) {
                    $uiSchema[$propertyName] = $data;
                }
            }
            self::sortProperties($propertyOrders);
            if (!empty($propertyOrders)) {
                $orders = array_keys($propertyOrders);
                $noneOrderedProperties = array_diff(array_keys($properties), array_keys($propertyOrders));
                if(count($noneOrderedProperties) > 0) {
                    array_push($orders, '*');
                }
                $uiSchema['ui:order'] = $orders;
            }
        } else if (isset($formSchema['anyOf'])) {
            $uiSchema = self::extractOneOf($formSchema['anyOf']);
        }else if (isset($formSchema['oneOf'])) {
            $uiSchema = self::extractOneOf($formSchema['oneOf']);
        }

        return self::extractUiSchema($formSchema) + $uiSchema;
    }

    private static function sortProperties(array &$data)
    {
        uasort($data, function($a, $b) {
            if ($a == $b) {
                return 0;
            }
            return ($a < $b) ? -1 : 1;
        });
    }

    protected static function extractArray(array &$formSchema) {
        $uiSchema = [];

        $items = &$formSchema['items'];
        if(self::isIndexedArray($items)) { // json array schema
            foreach($items as &$item) {
                $uiSchema['items'][]= self::extract($item);
            }
        } else if (isset($items['$ref'])) {
            //@todo: array ref
            throw new \RuntimeException('$ref is not implemented yet');
        } else { // json object schema
            $uiSchema['items'] = (object)self::extractUiSchema($formSchema);
        }

        return self::extractUiSchema($formSchema) + $uiSchema;
    }


    protected static function extractUiSchema(array &$formSchema) {
        if (isset($formSchema['uiSchema'])) {
            $uiSchema = $formSchema['uiSchema'];
             unset($formSchema['uiSchema']);
             return $uiSchema;
        }

        return [];
    }

    private static function extractOneOf(array &$oneOf) {
        $uiSchema = [];

        foreach($oneOf as &$any) {
            $uiSchema[] = self::extract($any);
        }

        return $uiSchema;
    }

    /**
     * A very simple way to check whether the array is indexed.
     * only check its first item, enough for our case.
     *
     * @param  array   $items
     *
     * @return boolean
     */
    private static function isIndexedArray(array $items) {
        $key = key($items);

        return is_int($key);
    }
}
