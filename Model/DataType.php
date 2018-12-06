<?php

namespace Videni\Bundle\RestBundle\Model;

/**
 * Provides a list of all the supported data-types of an incoming values which are implemented "out of the box".
 * New data-types can be added by implementing a value normalization processors.
 * @see \Videni\Bundle\RestBundle\Request\ValueNormalizer
 * Also provides a set of methods to simplify work with definition of complex data-types,
 * like nested and extended associations.
 */
final class DataType
{
    const INTEGER          = 'integer';
    const SMALLINT         = 'smallint';
    const BIGINT           = 'bigint';
    const UNSIGNED_INTEGER = 'unsignedInteger';
    const STRING           = 'string';
    const BOOLEAN          = 'boolean';
    const DECIMAL          = 'decimal';
    const FLOAT            = 'float';
    const DATETIME         = 'datetime';
    const DATE             = 'date';
    const TIME             = 'time';
    const PERCENT          = 'percent';
    const MONEY            = 'money';
    const DURATION         = 'duration';
    const GUID             = 'guid';
    const ENTITY_TYPE      = 'entityType';
    const ENTITY_CLASS     = 'entityClass';
    const ORDER_BY         = 'orderBy';

    private const NESTED_OBJECT                   = 'nestedObject';
    private const COLLECTION_ASSOCIATION                   = 'collection';
    private const ASSOCIATION_AS_FIELD_DATA_TYPES = ['object', 'scalar'];

    /**
     * Checks whether the field represents a nested object.
     *
     * @param string $dataType
     *
     * @return bool
     */
    public static function isNestedObject($dataType)
    {
        return self::NESTED_OBJECT === $dataType;
    }

    public static function isArrayAssociation($dataType)
    {
        return self::COLLECTION_ASSOCIATION === $dataType;
    }

    /**
     * Usually, to increase readability, "array" data-type is used for "to-many" associations
     * and "object" or "scalar" data-type is used for "to-one" associations.
     * The "object" is usually used if a value of such field contains several properties.
     * The "scalar" is usually used if a value of such field contains a scalar value.
     * Also "nestedObject" data-type, that is used to group several fields in one object,
     *
     * @param string $dataType
     *
     * @return bool
     */
    public static function isAssociationAsField($dataType)
    {
        return \in_array($dataType, self::ASSOCIATION_AS_FIELD_DATA_TYPES, true);
    }
}
