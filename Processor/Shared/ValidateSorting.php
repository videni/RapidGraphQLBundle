<?php

namespace App\Bundle\RestBundle\Processor\Shared;

use App\Bundle\RestBundle\Config\SortingConfig;
use App\Bundle\RestBundle\Processor\Context;
use Oro\Component\ChainProcessor\ContextInterface;
use Oro\Component\ChainProcessor\ProcessorInterface;
use App\Bundle\RestBundle\Filter\FilterNames;
use App\Bundle\RestBundle\Filter\FilterValue\FilterValue;
use App\Bundle\RestBundle\Config\Paginator\PaginatorConfig;
use Doctrine\Common\Collections\Criteria;

/**
 * Validates that sorting by requested field(s) is supported.
 */
class ValidateSorting implements ProcessorInterface
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
    public function process(ContextInterface $context)
    {
        /** @var Context $context */

        if ($context->hasQuery()) {
            // a query is already built
            return;
        }

        $sortFilterName = $this->filterNames->getSortFilterName();
        if (!$context->getFilters()->has($sortFilterName)) {
            // no sort filter
            return;
        }

        $sortFilterValue = $context->getFilterValues()->get($sortFilterName);
        if (null === $sortFilterValue) {
            // sorting is not requested
            return;
        }

        $paginatorConfig = $context->getPaginatorConfig();
        if (null === $paginatorConfig || empty($paginatorConfig->getSortings())) {
            return;
        }

        $unsupportedFields = $this->validateSortValues($sortFilterValue, $paginatorConfig);
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
    private function validateSortValues(FilterValue $filterValue, PaginatorConfig $paginatorConfig): array
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
            if (!$paginatorConfig->hasSorting($fieldName)) {
                $unsupportedFields[] = $fieldName;
            } else {
                $sortingConfig = $paginatorConfig->getSorting($fieldName);
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
