<?php

namespace Videni\Bundle\RestBundle\Processor\Shared;

use Doctrine\Common\Collections\Criteria;
use Videni\Bundle\RestBundle\Filter\FilterCollection;
use Videni\Bundle\RestBundle\Filter\SortFilter;
use Videni\Bundle\RestBundle\Processor\Context;
use Videni\Bundle\RestBundle\Model\DataType;
use Oro\Component\ChainProcessor\ContextInterface;
use Oro\Component\ChainProcessor\ProcessorInterface;
use Videni\Bundle\RestBundle\Util\DoctrineHelper;
use Videni\Bundle\RestBundle\Filter\FilterNames;
use Videni\Bundle\RestBundle\Config\Paginator\PaginatorConfig;

/**
 * Sets default sorting for different kind of requests.
 * The default sorting expression is "identifier field ASC".
 */
class AddSorting implements ProcessorInterface
{
    private $doctrineHelper;

    private $filterNames;
    /**
     * @param FilterNamesRegistry $filterNamesRegistry
     */
    public function __construct(
        DoctrineHelper $doctrineHelper,
        FilterNames $filterNames
    ) {
        $this->doctrineHelper = $doctrineHelper;
        $this->filterNames = $filterNames;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContextInterface $context)
    {
        /** @var Context $context */
        if ($context->hasQuery()) {
            // a query is already built
            return;
        }

        $entityClass =  $context->getClassName();

        if (!$this->doctrineHelper->isManageableEntityClass($entityClass)) {
            // only manageable entities or resources based on manageable entities can have the metadata
            return ;
        }

        $metadata = $this->doctrineHelper->getEntityMetadataForClass($entityClass);

        $paginatorConfig = $context->getPaginatorConfig();
        if (null === $paginatorConfig) {
            return;
        }

        $filters = $context->getFilters();

        if (empty($paginatorConfig->getSortings())) {
            $this->addDefaultSortFilter(
                $this->filterNames->getSortFilterName(),
                $context->getFilters(),
                $paginatorConfig
            );
        } else {
            $this->addConfiguredSortFilter($filters, $paginatorConfig);
        }
    }

    protected function addConfiguredSortFilter(FilterCollection $filters, PaginatorConfig $paginatorConfig)
    {
        $filters->add(
            $this->filterNames->getSortFilterName(),
            new SortFilter(
                DataType::ORDER_BY,
                $this->getSortFilterDescription(),
                function () use ($paginatorConfig) {
                    $orderBy = [];
                    foreach ($paginatorConfig->getSortings() as $sortingName => $sortingConfig) {
                        $fieldName = $sortingConfig->getPropertyPath();
                        $orderBy[$fieldName] = $sortingConfig->getOrder();
                    }

                    return $orderBy;
                },
                function ($value) {
                    return $this->convertDefaultValueToString($value);
                }
            )
        );
    }

    /**
     * @param string                 $filterName
     * @param FilterCollection       $filters
     * @param EntityDefinitionConfig $config
     */
    protected function addDefaultSortFilter(
        string $filterName,
        FilterCollection $filters,
        ClassMetadata  $metadata,
        PaginatorConfig  $paginatorConfig
    ): void {
        if (!$filters->has($filterName)) {
            $filters->add(
                $filterName,
                new SortFilter(
                    DataType::ORDER_BY,
                    $this->getSortFilterDescription(),
                    function () use ($paginatorConfig, $metadata) {
                        return $this->getDefaultValue($metadata, $paginatorConfig);
                    },
                    function ($value) {
                        return $this->convertDefaultValueToString($value);
                    }
                )
            );
        }
    }

    /**
     * @return string
     */
    protected function getSortFilterDescription(): string
    {
        return 'Result sorting. Comma-separated fields, e.g. \'field1,-field2\'.';
    }

    /**
     * @param EntityDefinitionConfig $config
     *
     * @return array [field name => direction, ...]
     */
    protected function getDefaultValue(ClassMetadata $metadata, PaginatorConfig $paginatorConfig): array
    {
        $orderBy = [];
        $idFieldNames = $metadata->getIdentifierFieldNames();
        if (!empty($idFieldNames)) {
            foreach ($idFieldNames as $fieldName) {
                $filterConfig = $paginatorConfig->getFilter($fieldName);
                if (null !== $filterConfig) {
                    $fieldName = $filterConfig->getPropertyPath($fieldName);
                }
                $orderBy[$fieldName] = Criteria::ASC;
            }
        }

        return $orderBy;
    }

    /**
     * @param array|null $value
     *
     * @return string
     */
    protected function convertDefaultValueToString(?array $value): string
    {
        $result = [];
        if (null !== $value) {
            foreach ($value as $field => $order) {
                $result[] = (Criteria::DESC === $order ? '-' : '') . $field;
            }
        }

        return \implode(',', $result);
    }
}
