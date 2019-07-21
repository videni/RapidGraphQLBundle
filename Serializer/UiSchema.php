<?php

namespace Videni\Bundle\RestBundle\Serializer;

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
        $type = $formSchema['type'];

        if ('object' === $type) {
            return self::extractObject($formSchema);
        }

        if ('array' === $type && isset($formSchema['items'])) {
           return self::extractArray($formSchema);
        }

        if (in_array($type, ['number', 'boolean', 'integer', 'number', 'string'])) {
            return self::extractUiOptions($formSchema);
        }

        throw new \Exception(sprintf(
            'JSON Schema type %s is not supported, available are %s',
            $type,
            implode(',', [ 'object', 'boolean', 'integer', 'number', 'object', 'array', 'string'])
        ));
    }

    protected static function extractObject(array &$formSchema)
    {
        $uiSchema = null;

        if (isset($formSchema['properties'])) {
            $properties = &$formSchema['properties'];
            foreach($properties as $propertyName => &$property) {
                $data = self::extract($property);
                if (!empty($data)) {
                    $uiSchema[$propertyName] = $data;
                }
            }
        } else if (isset($formSchema['anyOf'])) {
            $uiSchema = self::extractOneOf($formSchema['anyOf']);
        }else if (isset($formSchema['oneOf'])) {
            $uiSchema = self::extractOneOf($formSchema['oneOf']);
        }

        return self::extractUiOptions($formSchema)+ $uiSchema;
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
            $uiSchema['items'] = self::extract($formSchema['items']);
        }

        return self::extractUiOptions($formSchema) + $uiSchema;
    }

    protected static function extractUiOptions(array &$formSchema) {
        $uiSchema = [];

        if (isset($formSchema['widget'])) {
            $uiSchema = ['ui:widget' => $formSchema['widget'] ];
            unset($formSchema['widget']);
        }

        if(isset($formSchema['ui'])) {
            $options = [];
            foreach($formSchema['ui'] as $key => $value) {
                if (!is_null($value)) {
                    $uiSchema['ui:'.$key ] = $value;
                }
            }

            unset($formSchema['ui']);
        }

        return $uiSchema;
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
