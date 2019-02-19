<?php

namespace Videni\Bundle\RestBundle\Grid;

use Doctrine\Common\Collections\Criteria;
use Videni\Bundle\RestBundle\Filter\FilterCollection;
use Videni\Bundle\RestBundle\Filter\SortFilter;
use Videni\Bundle\RestBundle\Processor\Context;
use Videni\Bundle\RestBundle\Model\DataType;
use Videni\Bundle\RestBundle\Util\DoctrineHelper;
use Videni\Bundle\RestBundle\Filter\FilterNames;
use Videni\Bundle\RestBundle\Config\Grid\Grid;
use Doctrine\ORM\Mapping\ClassMetadata;

/**
 * Sets default sorting for different kind of requests.
 * The default sorting expression is "identifier field ASC".
 */
class AddSorting
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
    public function process($entityClass, Grid $grid, FilterCollection $filters)
    {
        $metadata = $this->doctrineHelper->getEntityMetadataForClass($entityClass);
        if (empty($grid->getSortings())) {
            $this->addDefaultSortFilter(
                $this->filterNames->getSortFilterName(),
                $filters,
                $metadata,
                $grid
            );
        } else {
            $this->addConfiguredSortFilter($filters, $grid);
        }
    }

    protected function addConfiguredSortFilter(FilterCollection $filters, Grid $grid)
    {
        $filters->add(
            $this->filterNames->getSortFilterName(),
            new SortFilter(
                DataType::ORDER_BY,
                $this->getSortFilterDescription(),
                function () use ($grid) {
                    $orderBy = [];
                    foreach ($grid->getSortings() as $field) {
                        $fieldName = $field->getPropertyPath();
                        $orderBy[$fieldName] = $field->getSorting();
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
     * @param ClassMetadata       $filters
     * @param Grid $config
     */
    protected function addDefaultSortFilter(
        string $filterName,
        FilterCollection $filters,
        ClassMetadata  $metadata,
        Grid  $grid
    ): void {
        if (!$filters->has($filterName)) {
            $filters->add(
                $filterName,
                new SortFilter(
                    DataType::ORDER_BY,
                    $this->getSortFilterDescription(),
                    function () use ($grid, $metadata) {
                        return $this->getDefaultValue($metadata, $grid);
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
    protected function getDefaultValue(ClassMetadata $metadata, Grid $grid): array
    {
        $orderBy = [];
        $idFieldNames = $metadata->getIdentifierFieldNames();
        if (!empty($idFieldNames)) {
            foreach ($idFieldNames as $fieldName) {
                $filterConfig = $grid->getFilter($fieldName);
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
