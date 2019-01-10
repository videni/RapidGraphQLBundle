<?php

namespace Videni\Bundle\RestBundle\Paginator;

use Doctrine\ORM\Mapping\ClassMetadata;
use Videni\Bundle\RestBundle\Filter\ComparisonFilter;
use Videni\Bundle\RestBundle\Filter\FieldAwareFilterInterface;
use Videni\Bundle\RestBundle\Filter\Factory\FilterFactoryInterface;
use Videni\Bundle\RestBundle\Filter\StandaloneFilter;
use Videni\Bundle\RestBundle\Processor\Context;
use Videni\Bundle\RestBundle\Util\DoctrineHelper;
use Videni\Bundle\RestBundle\Filter\FilterCollection;
use Videni\Bundle\RestBundle\Context\ResourceContext;
use Videni\Bundle\RestBundle\Config\Paginator\PaginatorConfig;

/**
 * Registers filters according to the "filters" configuration section.
 */
class RegisterConfiguredFilter extends RegisterFilters
{
    private const ASSOCIATION_ALLOWED_OPERATORS = [
        ComparisonFilter::EQ,
        ComparisonFilter::NEQ,
        ComparisonFilter::EXISTS,
        ComparisonFilter::NEQ_OR_NULL
    ];

    private const COLLECTION_ASSOCIATION_ALLOWED_OPERATORS = [
        ComparisonFilter::EQ,
        ComparisonFilter::NEQ,
        ComparisonFilter::EXISTS,
        ComparisonFilter::NEQ_OR_NULL,
        ComparisonFilter::CONTAINS,
        ComparisonFilter::NOT_CONTAINS
    ];

    private const SINGLE_IDENTIFIER_EXCLUDED_OPERATORS = [
        ComparisonFilter::EXISTS,
        ComparisonFilter::NEQ_OR_NULL
    ];


    /** @var DoctrineHelper */
    protected $doctrineHelper;

    /**
     * @param FilterFactoryInterface $filterFactory
     * @param DoctrineHelper         $doctrineHelper
     */
    public function __construct(
        FilterFactoryInterface $filterFactory,
        DoctrineHelper $doctrineHelper
    ) {
        parent::__construct($filterFactory);

        $this->doctrineHelper = $doctrineHelper;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function getFilters(ResourceContext $context, PaginatorConfig $paginatorConfig)
    {
        $resourceConfig = $context->getResourceConfig();

        $entityClass = $context->getClassName();
        if (!$this->doctrineHelper->isManageableEntityClass($entityClass)) {
            // only manageable entities or resources based on manageable entities can have the metadata
            return ;
        }

        $metadata = $this->doctrineHelper->getEntityMetadataForClass($entityClass);

        $idFieldName = $this->getSingleIdentifierFieldName($metadata);
        $associationNames = $this->getAssociationNames($metadata);
        $filters = new FilterCollection();

        $filtersConfig = $paginatorConfig->getFilters();
        foreach ($filtersConfig as $filterKey => $filterConfig) {
            if ($filters->has($filterKey)) {
                continue;
            }
            $propertyPath = $filterConfig->getPropertyPath();
            $filter = $this->createFilter($filterConfig, $propertyPath, $resourceConfig);
            if (null !== $filter) {
                if ($filter instanceof FieldAwareFilterInterface) {
                    if ($idFieldName && $filterKey === $idFieldName) {
                        $this->updateSingleIdentifierOperators($filter);
                    }
                    if (\in_array($propertyPath, $associationNames, true)) {
                        $this->updateAssociationOperators($filter, $filterConfig->isCollection());
                    }
                }

                $filters->add($filterKey, $filter);
            }
        }

        return $filters;
    }

    /**
     * @param EntityDefinitionConfig|null $config
     *
     * @return string|null
     */
    protected function getSingleIdentifierFieldName(?ClassMetadata $classMetadata)
    {
        $idFieldNames = $classMetadata->getIdentifierFieldNames();
        if (\count($idFieldNames) !== 1) {
            return null;
        }

        return \reset($idFieldNames);
    }

    /**
     * @param ClassMetadata|null $metadata
     *
     * @return string[]
     */
    protected function getAssociationNames(?ClassMetadata $metadata)
    {
        return null !== $metadata
            ? \array_keys($this->doctrineHelper->getIndexedAssociations($metadata))
            : [];
    }

    /**
     * @param StandaloneFilter $filter
     */
    private function updateSingleIdentifierOperators(StandaloneFilter $filter)
    {
        $filter->setSupportedOperators(
            \array_diff($filter->getSupportedOperators(), self::SINGLE_IDENTIFIER_EXCLUDED_OPERATORS)
        );
    }

    /**
     * @param StandaloneFilter $filter
     * @param bool             $isCollection
     */
    private function updateAssociationOperators(StandaloneFilter $filter, bool $isCollection)
    {
        $allowedOperators = $isCollection
            ? self::COLLECTION_ASSOCIATION_ALLOWED_OPERATORS
            : self::ASSOCIATION_ALLOWED_OPERATORS;
        if ([] !== \array_diff($filter->getSupportedOperators(), $allowedOperators)) {
            $filter->setSupportedOperators($allowedOperators);
        }
    }
}
