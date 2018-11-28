<?php

namespace Videni\Bundle\RestBundle\Processor\Shared;

use Videni\Bundle\RestBundle\Config\Paginator\FilterConfig;
use Videni\Bundle\RestBundle\Filter\FieldAwareFilterInterface;
use Videni\Bundle\RestBundle\Filter\Factory\FilterFactoryInterface;
use Videni\Bundle\RestBundle\Filter\MetadataAwareFilterInterface;
use Videni\Bundle\RestBundle\Filter\StandaloneFilter;
use Videni\Bundle\RestBundle\Processor\Context;
use Oro\Component\ChainProcessor\ProcessorInterface;

/**
 * Abstract class for register filters processor.
 */
abstract class RegisterFilters implements ProcessorInterface
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
    protected function createFilter(FilterConfig $filterConfig, $propertyPath, Context $context)
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
            if ($filter instanceof MetadataAwareFilterInterface) {
                $metadata = $context->getMetadata();
                if (null === $metadata) {
                    throw new \LogicException(\sprintf(
                        'The metadata for class "%s" does not exist, but it required for the filter by "%s"',
                        $context->getClassName(),
                        $propertyPath
                    ));
                }
                $filter->setMetadata($metadata);
            }
        }

        return $filter;
    }
}
