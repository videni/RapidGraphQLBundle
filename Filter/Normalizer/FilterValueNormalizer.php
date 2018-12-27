<?php

namespace Videni\Bundle\RestBundle\Filter\Normalizer;

use Videni\Bundle\RestBundle\Model\DataType;

/**
 * Provides a way to convert incoming value to concrete data-type.
 */
class FilterValueNormalizer
{
    const DEFAULT_REQUIREMENT = '.+';

    /** @var string[] */
    protected $requirements = [];

    private $normalizerCompositor;

    public function __construct(NormalizerCompositor $normalizerCompositor)
    {
        $this->normalizerCompositor = $normalizerCompositor;
    }

    /**
     * Converts a value to the given data-type.
     *
     * @param mixed       $value          A value to be converted.
     * @param string      $dataType       The data-type.
     * @param bool        $isArrayAllowed Whether a value can be an array.
     * @param bool        $isRangeAllowed Whether a value can be a pair of "from" and "to" values.
     *
     * @return mixed
     */
    public function normalizeValue(
        $value,
        $dataType,
        $isArrayAllowed = false,
        $isRangeAllowed = false
    ) {
        return $this
            ->doNormalization($dataType, $value, $isArrayAllowed, $isRangeAllowed)
            ->getResult();
    }

    /**
     * Gets a regular expression that can be used to validate a value of the given data-type.
     *
     * @param string      $dataType       The data-type.
     * @param bool        $isArrayAllowed Whether a value can be an array.
     * @param bool        $isRangeAllowed Whether a value can be a pair of "from" and "to" values.
     *
     * @return string
     */
    public function getRequirement(
        $dataType,
        $isArrayAllowed = false,
        $isRangeAllowed = false
    ) {
        $requirementKey = $dataType . '|' . $this->buildCacheKey($isArrayAllowed, $isRangeAllowed);
        if (!array_key_exists($requirementKey, $this->requirements)) {
            $context = $this->doNormalization($dataType, null, $isArrayAllowed, $isRangeAllowed);

            $this->requirements[$requirementKey] = $context->getRequirement() ?: self::DEFAULT_REQUIREMENT;
        }

        return $this->requirements[$requirementKey];
    }

    /**
     * @param string      $dataType
     * @param RequestType     * @param mixed       $value
     * @param bool        $isArrayAllowed
     * @param bool        $isRangeAllowed
     *
     * @return NormalizerContext
     * @throws \Exception
     */
    protected function doNormalization(
        $dataType,
        $value,
        $isArrayAllowed,
        $isRangeAllowed
    ) {
        $context = new NormalizerContext();

        $context->setDataType($dataType);
        $context->setResult($value);
        $context->setArrayAllowed($isArrayAllowed);
        $context->setRangeAllowed($isRangeAllowed);

        $this->normalizerCompositor->normalize($context);

        return $context;
    }
}
