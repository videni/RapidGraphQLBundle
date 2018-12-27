<?php

namespace Videni\Bundle\RestBundle\Paginator;

use Videni\Bundle\RestBundle\Filter\ComparisonFilter;
use Videni\Bundle\RestBundle\Filter\FilterValue;
use Videni\Bundle\RestBundle\Filter\StandaloneFilter;
use Videni\Bundle\RestBundle\Model\Range;
use Videni\Bundle\RestBundle\Model\DataType;
use Videni\Bundle\RestBundle\Filter\Normalizer\FilterValueNormalizer;
use Videni\Bundle\RestBundle\Filter\FilterCollection;
use Videni\Bundle\RestBundle\Util\DoctrineHelper;
use Doctrine\ORM\Mapping\ClassMetadata;
use Videni\Bundle\RestBundle\Filter\FilterValue\FilterValueAccessor;
use Videni\Bundle\RestBundle\Paginator\EntityIdTransformer;
use Videni\Bundle\RestBundle\Context\ResourceContext;

/**
 * Converts values of all requested filters according to the type of the filters.
 * Validates that all requested filters are supported.
 */
class NormalizeFilterValues
{
    /** @var ValueNormalizer */
    private $filterValueNormalizer;

    private $doctrineHelper;

    private $entityIdTransfomer;

    public function __construct(
        FilterValueNormalizer $filterValueNormalizer,
        DoctrineHelper $doctrineHelper,
        EntityIdTransformer $entityIdTransfomer
    ) {
        $this->filterValueNormalizer = $filterValueNormalizer;
        $this->doctrineHelper = $doctrineHelper;
        $this->entityIdTransformer = $entityIdTransfomer;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize(ResourceContext $context, FilterCollection $filters, FilterValueAccessor $filterValues)
    {
        $entityClass = $context->getClassName();
        $metadata = $this->doctrineHelper->getEntityMetadataForClass($entityClass);

        foreach ($filterValues->getAll() as $filterKey => $filterValue) {
            if ($filters->has($filterKey)) {
                $filter = $filters->get($filterKey);
                if ($filter instanceof StandaloneFilter) {
                    $value = $this->normalizeFilterValue(
                        $filter,
                        $filterValue->getValue(),
                        $filterValue->getOperator(),
                        $metadata
                    );
                    $filterValue->setValue($value);
                }
            }
        }
    }

    /**
     * @param RequestType
     * @param StandaloneFilter    $filter
     * @param mixed               $value
     * @param string|null         $operator
     * @param EntityMetadata|null $metadata
     *
     * @return mixed
     */
    private function normalizeFilterValue(
        StandaloneFilter $filter,
        $value,
        ?string $operator,
        ?ClassMetadata $metadata
    ) {
        $dataType = $filter->getDataType();
        $isArrayAllowed = $filter->isArrayAllowed($operator);
        $isRangeAllowed = $filter->isRangeAllowed($operator);
        if (ComparisonFilter::EXISTS === $operator) {
            $dataType = DataType::BOOLEAN;
            $isArrayAllowed = false;
            $isRangeAllowed = false;
        } elseif (null !== $metadata && $filter instanceof ComparisonFilter) {
            $fieldName = $filter->getField();
            if ($fieldName) {
                if ($metadata->hasAssociation($fieldName)) {
                    return $this->normalizeIdentifierValue(
                        $value,
                        $isArrayAllowed,
                        $isRangeAllowed,
                        $metadata->getAssociationMapping($fieldName)
                    );
                }
                $idFieldNames = $metadata->getIdentifierFieldNames();
                if (\count($idFieldNames) === 1) {
                    if ($fieldName === $idFieldNames[0]) {
                        return $this->normalizeIdentifierValue(
                            $value,
                            $isArrayAllowed,
                            $isRangeAllowed,
                            $metadata
                        );
                    }
                }
            }
        }

        return $this->filterValueNormalizer->normalizeValue(
            $value,
            $dataType,
            $isArrayAllowed,
            $isRangeAllowed
        );
    }

    /**
     * @param mixed          $value
     * @param bool           $isArrayAllowed
     * @param bool           $isRangeAllowed
     * @param RequestType
     * @param ClassMetadata $metadata
     *
     * @return mixed
     */
    private function normalizeIdentifierValue(
        $value,
        bool $isArrayAllowed,
        bool $isRangeAllowed,
        array $associationMapping
    ) {
        $metadata = $this->doctrineHelper->getEntityMetadataForClass($associationMapping['targetEntity']);

        $value = $this->filterValueNormalizer->normalizeValue(
            $value,
            DataType::STRING,
            $isArrayAllowed,
            $isRangeAllowed
        );

        if (\is_array($value)) {
            $normalizedValue = [];
            foreach ($value as $val) {
                $normalizedValue[] = $this->entityIdTransformer->reverseTransform($val, $metadata);
            }

            return $normalizedValue;
        }

        if ($value instanceof Range) {
            $value->setFromValue($this->entityIdTransformer->reverseTransform($value->getFromValue(), $metadata));
            $value->setToValue($this->entityIdTransformer->reverseTransform($value->getToValue(), $metadata));

            return $value;
        }

        return $this->entityIdTransformer->reverseTransform($value, $metadata);
    }
}
