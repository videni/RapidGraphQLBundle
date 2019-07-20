<?php

namespace Videni\Bundle\RestBundle\Serializer;

class UiSchema {
    public static function extract(array &$formSchema) {
        $type = $formSchema['type'];

        if ('object' === $type) {
            return self::extractObject($formSchema);
        }

        if ('array' === $type && isset($formSchema['items'])) {
           return self::extractArray($formSchema);
        }

        if (in_array($type, ['number', 'boolean', 'integer', 'number', 'string'])) {
            return self::extractStringLike($formSchema);
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
            foreach($formSchema['properties'] as $propertyName => $property) {
                $data = self::extract($property);
                if (!empty($data)) {
                    $uiSchema[$propertyName] = $data;
                }
            }
        } else if (isset($formSchema['anyOf'])|| isset($formSchema['oneOf'])) {
            $anyOf = $formSchema['anyOf'] || $formSchema['oneOf'];
            foreach($anyOf as $any) {
                $uiSchema[] = self::extract($any);
            }
        }

        return $uiSchema;
    }

    protected static function extractArray(array &$formSchema) {
        $uiSchema = [];

        $items = $formSchema['items'];
        if(self::isIndexedArray($items)) {
            foreach($items as $item) {
                $uiSchema[]= self::extract($item);
            }
        } else if (isset($items['$ref'])) {
            //@todo: array ref
            throw new \RuntimeException('$ref is not implemented yet');
        } else {
            $uiSchema = self::extract($formSchema['items']);
        }

        return $uiSchema;
    }

    protected static function extractStringLike(array &$formSchema) {
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
