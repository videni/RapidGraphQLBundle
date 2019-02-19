<?php

namespace Videni\Bundle\RestBundle\Grid;

use Videni\Bundle\RestBundle\Config\Sorting;
use Videni\Bundle\RestBundle\Processor\Context;
use Videni\Bundle\RestBundle\Filter\FilterNames;
use Videni\Bundle\RestBundle\Filter\FilterValue\FilterValue;
use Videni\Bundle\RestBundle\Config\Grid\Grid;
use Doctrine\Common\Collections\Criteria;
use Videni\Bundle\RestBundle\Filter\FilterValue\FilterValueAccessor;
use Videni\Bundle\RestBundle\Filter\FilterCollection;

/**
 * Validates that sorting by requested field(s) is supported.
 */
class ValidateSorting
{
    /** @var FilterNamesRegistry */
    private $filterNames;

    /**
     * @param FilterNames $filterNames
     */
    public function __construct(
        FilterNames $filterNames
    ) {
        $this->filterNames = $filterNames;
    }

    /**
     * {@inheritdoc}
     */
    public function validate(FilterCollection $filters, FilterValueAccessor $filterValues, Grid $grid)
    {
        $sortFilterName = $this->filterNames->getSortFilterName();
        if (!$filters->has($sortFilterName)) {
            // no sort filter
            return;
        }

        $sortFilterValue = $filterValues->get($sortFilterName);
        if (null === $sortFilterValue) {
            // sorting is not requested
            return;
        }

        $unsupportedFields = $this->validateSortValues($sortFilterValue, $grid);
        if (!empty($unsupportedFields)) {
             throw new \RuntimeException($this->getValidationErrorMessage($unsupportedFields));
        }
    }

    /**
     * @param string[] $unsupportedFields
     *
     * @return string
     */
    private function getValidationErrorMessage(array $unsupportedFields): string
    {
        return \sprintf(
            'Sorting by "%s" field%s not supported.',
            \implode(', ', $unsupportedFields),
            \count($unsupportedFields) === 1 ? ' is' : 's are'
        );
    }

    /**
     * @param FilterValue $filterValue
     * @param Context     $context
     *
     * @return string[] The list of fields that cannot be used for sorting
     */
    private function validateSortValues(FilterValue $filterValue, Grid $grid): array
    {
        $orderBy = $filterValue->getValue();
        if (empty($orderBy)) {
            return [];
        }
        if (!is_array($orderBy)) {
            $orderBy = [ $orderBy => Criteria::DESC ];
            $filterValue->setValue($orderBy);
        }

        $unsupportedFields = [];
        foreach ($orderBy as $fieldName => $direction) {
            if (!$grid->hasSorting($fieldName)) {
                $unsupportedFields[] = $fieldName;
            } else {
                $sortingConfig = $grid->getSorting($fieldName);
                $propertyPath = $sortingConfig->getPropertyPath();
                if ($propertyPath) {
                    $this->renameSortField($filterValue, $fieldName, $propertyPath);
                }
            }
        }

        return $unsupportedFields;
    }

    /**
     * @param FilterValue $filterValue
     * @param string      $oldFieldName
     * @param string      $newFieldName
     */
    /**
     * @param FilterValue $filterValue
     * @param string      $oldFieldName
     * @param string      $newFieldName
     */
    private function renameSortField(FilterValue $filterValue, string $oldFieldName, string $newFieldName): void
    {
        $updatedOrderBy = [];
        $orderBy = $filterValue->getValue();
        foreach ($orderBy as $fieldName => $direction) {
            if ($fieldName === $oldFieldName) {
                $fieldName = $newFieldName;
            }
            $updatedOrderBy[$fieldName] = $direction;
        }
        $filterValue->setValue($updatedOrderBy);
    }
}
