<?php

namespace App\Bundle\RestBundle\Processor\Shared;

use Doctrine\ORM\Mapping\ClassMetadata;
use App\Bundle\RestBundle\Config\PaginatorConfig;
use App\Bundle\RestBundle\Filter\ComparisonFilter;
use App\Bundle\RestBundle\Filter\FieldAwareFilterInterface;
use App\Bundle\RestBundle\Filter\Factory\FilterFactoryInterface;
use App\Bundle\RestBundle\Filter\StandaloneFilter;
use App\Bundle\RestBundle\Processor\Context;
use App\Bundle\RestBundle\Util\DoctrineHelper;
use Oro\Component\ChainProcessor\ContextInterface;
use App\Bundle\RestBundle\Config\Paginator\PaginatorConfigProvider;

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
    private const SINGLE_IDENTIFIER_EXCLUDED_OPERATORS = [
        ComparisonFilter::EXISTS,
        ComparisonFilter::NEQ_OR_NULL
    ];

    /** @var DoctrineHelper */
    protected $doctrineHelper;

    protected $paginatorConfigProvider;

    /**
     * @param FilterFactoryInterface $filterFactory
     * @param DoctrineHelper         $doctrineHelper
     */
    public function __construct(
        FilterFactoryInterface $filterFactory,
        DoctrineHelper $doctrineHelper,
        PaginatorConfigProvider $paginatorConfigProvider
    ) {
        parent::__construct($filterFactory);

        $this->doctrineHelper = $doctrineHelper;
        $this->paginatorConfigProvider = $paginatorConfigProvider;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function process(ContextInterface $context)
    {
        $resourceConfig = $context->getResourceConfig();

        $operationName = $context->getOperationName();

        $paginatorName =  $resourceConfig->getOperation($operationName)->getPaginator();
        if (null === $paginatorName) {
            return;
        }

        $entityClass =  $context->getClassName();

        $paginatorConfig = $this->paginatorConfigProvider->get($paginatorName);
        $context->setPaginatorConfig($paginatorConfig);

        if (!$this->doctrineHelper->isManageableEntityClass($entityClass)) {
            // only manageable entities or resources based on manageable entities can have the metadata
            return ;
        }

        $metadata = $this->doctrineHelper->getEntityMetadataForClass($entityClass);

        $idFieldName = $this->getSingleIdentifierFieldName($metadata);
        $associationNames = $this->getAssociationNames($metadata);
        $filters = $context->getFilters();

        $filtersConfig = $paginatorConfig->getFilters();
        foreach ($filtersConfig as $filterKey => $filter) {
            if ($filters->has($filterKey)) {
                continue;
            }
            $propertyPath = $filter->getPropertyPath($filterKey);
            $filter = $this->createFilter($filter, $propertyPath, $context);
            if (null !== $filter) {
                if ($filter instanceof FieldAwareFilterInterface) {
                    if ($idFieldName && $filterKey === $idFieldName) {
                        $filter->setSupportedOperators(
                            \array_diff($filter->getSupportedOperators(), self::SINGLE_IDENTIFIER_EXCLUDED_OPERATORS)
                        );
                    }
                    // @todo BAP-11881. Update this code when NEQ operator for to-many collection
                    // will be implemented in App\Bundle\RestBundle\Filter\ComparisonFilter
                    if (null !== $metadata && $this->isCollection($metadata, $propertyPath)) {
                        $filter->setSupportedOperators([StandaloneFilter::EQ]);
                    }
                    // only EQ, NEQ and EXISTS operators should be available for association filters
                    if (\in_array($propertyPath, $associationNames, true) &&
                        [] !== \array_diff($filter->getSupportedOperators(), self::ASSOCIATION_ALLOWED_OPERATORS)
                    ) {
                        $filter->setSupportedOperators(self::ASSOCIATION_ALLOWED_OPERATORS);
                    }
                }

                $filters->add($filterKey, $filter);
            }
        }
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
     * @param ClassMetadata $metadata
     * @param string        $propertyPath
     *
     * @return bool
     */
    protected function isCollection(ClassMetadata $metadata, $propertyPath)
    {
        $isCollection = false;
        $path = \explode('.', $propertyPath);
        foreach ($path as $filterName) {
            if ($metadata->isCollectionValuedAssociation($filterName)) {
                $isCollection = true;
                break;
            }
            if (!$metadata->hasAssociation($filterName)) {
                break;
            }

            $metadata = $this->doctrineHelper->getEntityMetadataForClass(
                $metadata->getAssociationTargetClass($filterName)
            );
        }

        return $isCollection;
    }
}
