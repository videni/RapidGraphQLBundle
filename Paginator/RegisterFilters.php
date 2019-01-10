<?php

namespace Videni\Bundle\RestBundle\Paginator;

use Videni\Bundle\RestBundle\Config\Paginator\FilterConfig;
use Videni\Bundle\RestBundle\Filter\FieldAwareFilterInterface;
use Videni\Bundle\RestBundle\Filter\Factory\FilterFactoryInterface;
use Videni\Bundle\RestBundle\Filter\MetadataAwareFilterInterface;
use Videni\Bundle\RestBundle\Filter\StandaloneFilter;
use Videni\Bundle\RestBundle\Config\Resource\ResourceConfig;
use Videni\Bundle\RestBundle\Filter\CollectionAwareFilterInterface;

/**
 * Abstract class for register filters processor.
 */
abstract class RegisterFilters
{
    /** @var FilterFactoryInterface */
    protected $filterFactory;

    /**
     * @param FilterFactoryInterface $filterFactory
     */
    public function __construct(FilterFactoryInterface $filterFactory)
    {
        $this->filterFactory = $filterFactory;
    }

    /**
     * @param FilterConfig $filterConfig
     * @param string            $propertyPath
     * @param Context           $context
     *
     * @return StandaloneFilter|null
     */
    protected function createFilter(FilterConfig $filterConfig, $propertyPath, ResourceConfig $resourceConfig)
    {
        $filterOptions = $filterConfig->getOptions();
        if (null === $filterOptions) {
            $filterOptions = [];
        }
        $filterType = $filterConfig->getType();

        $filter = $this->filterFactory->createFilter($filterType, $filterOptions);
        if (null !== $filter) {
            $filter->setArrayAllowed($filterConfig->isArrayAllowed());
            $filter->setRangeAllowed($filterConfig->isRangeAllowed());
            $filter->setDescription($filterConfig->getDescription());
            $operators = $filterConfig->getOperators();
            if (!empty($operators)) {
                $filter->setSupportedOperators($operators);
            }
            if ($filter instanceof FieldAwareFilterInterface) {
                $filter->setField($propertyPath);
            }
            if ($filterConfig->isCollection()) {
                if ($filter instanceof CollectionAwareFilterInterface) {
                    $filter->setCollection(true);
                } else {
                    throw new \LogicException(\sprintf(
                        'The filter by "%s" does not support the "collection" option',
                        $propertyPath
                    ));
                }
            }
            if ($filter instanceof MetadataAwareFilterInterface) {
                $filter->setMetadata($resourceConfig);
            }
        }

        return $filter;
    }
}
