<?php

namespace App\Bundle\RestBundle\Processor\Shared;

use App\Bundle\RestBundle\Config\FilterConfig;
use App\Bundle\RestBundle\Filter\FieldAwareFilterInterface;
use App\Bundle\RestBundle\Filter\FilterFactoryInterface;
use App\Bundle\RestBundle\Filter\MetadataAwareFilterInterface;
use App\Bundle\RestBundle\Filter\RequestAwareFilterInterface;
use App\Bundle\RestBundle\Filter\StandaloneFilter;
use App\Bundle\RestBundle\Processor\Context;
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
