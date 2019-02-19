<?php

namespace Videni\Bundle\RestBundle\Grid;

use Videni\Bundle\RestBundle\Model\DataType;
use Videni\Bundle\RestBundle\Filter\Normalizer\FilterValueNormalizer;
use Doctrine\ORM\Mapping\ClassMetadata;

/**
 * Transforms entity identifier value to and from a string representation used in REST API.
 */
class EntityIdTransformer
{
    /** A symbol to separate fields inside the composite identifier */
    private const COMPOSITE_ID_SEPARATOR = ';';

    /** @var ValueNormalizer */
    protected $filterValueNormalizer;

    /**
     * @param ValueNormalizer $filterValueNormalizer
     */
    public function __construct(FilterValueNormalizer $filterValueNormalizer)
    {
        $this->filterValueNormalizer = $filterValueNormalizer;
    }

    /**
     * {@inheritdoc}
     */
    public function transform($id, ClassMetadata $metadata)
    {
        return \is_array($id)
            ? \http_build_query($id, '', self::COMPOSITE_ID_SEPARATOR)
            : (string)$id;
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($value, ClassMetadata $metadata)
    {
        $idFieldNames = $metadata->getIdentifierFieldNames();
        if (\count($idFieldNames) === 1) {
            $value = $this->reverseTransformSingleId(
                $value,
                $this->getSingleIdDataType($metadata)
            );
        } else {
            $value = $this->reverseTransformCompositeEntityId($value, $metadata);
        }

        return $value;
    }

    /**
     * @param ClassMetadata $metadata
     *
     * @return string
     */
    protected function getSingleIdDataType(ClassMetadata $metadata)
    {
        $idFieldNames = $metadata->getIdentifierFieldNames();
        $idField = $metadata->getFieldMapping(\reset($idFieldNames));

        return null !== $idField
            ? $idField['type']
            : DataType::STRING;
    }

    /**
     * @param mixed  $value
     * @param string $dataType
     *
     * @return mixed
     */
    protected function reverseTransformSingleId($value, $dataType)
    {
        if (DataType::STRING === $dataType) {
            return $value;
        }

        return $this->filterValueNormalizer->normalizeValue($value, $dataType);
    }

    /**
     * @param string         $entityId
     * @param ClassMetadata $metadata
     *
     * @return array
     *
     * @throws \UnexpectedValueException if the given entity id cannot be normalized
     */
    protected function reverseTransformCompositeEntityId($entityId, ClassMetadata $metadata)
    {
        $fieldMap = [];
        foreach ($metadata->getIdentifierFieldNames() as $fieldName) {
            $fieldMap[$fieldName] = $metadata->getProperty($fieldName)->getDataType();
        }

        $normalized = [];
        foreach (\explode(self::COMPOSITE_ID_SEPARATOR, $entityId) as $item) {
            $val = \explode('=', $item);
            if (\count($val) !== 2) {
                throw new \UnexpectedValueException(
                    \sprintf(
                        'Unexpected identifier value "%s" for composite identifier of the entity "%s".',
                        $entityId,
                        $metadata->getClassName()
                    )
                );
            }

            list($key, $val) = $val;
            $val = \urldecode($val);

            if (!isset($fieldMap[$key])) {
                throw new \UnexpectedValueException(
                    \sprintf(
                        'The entity identifier contains the key "%s" '
                        . 'which is not defined in composite identifier of the entity "%s".',
                        $key,
                        $metadata->getClassName()
                    )
                );
            }

            $dataType = $fieldMap[$key];
            if (DataType::STRING !== $dataType) {
                $val = $this->filterValueNormalizer->normalizeValue($val, $dataType);
            }
            $normalized[$key] = $val;

            unset($fieldMap[$key]);
        }
        if (!empty($fieldMap)) {
            throw new \UnexpectedValueException(
                \sprintf(
                    'The entity identifier does not contain all keys '
                    . 'defined in composite identifier of the entity "%s".',
                    $metadata->getClassName()
                )
            );
        }

        return $normalized;
    }
}
