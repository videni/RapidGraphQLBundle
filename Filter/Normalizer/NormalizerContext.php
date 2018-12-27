<?php

namespace Videni\Bundle\RestBundle\Filter\Normalizer;

use Oro\Bundle\ApiBundle\Request\RequestType;
use Oro\Component\ChainProcessor\Context;

/**
 * The execution context filter value normalizer
 */
class NormalizerContext
{
    /** a data-type of a value */
    private $dataType;

    /** a regular expression that can be used to validate a value */
    private $requirement;

    /** determines if a value can be an array */
    private $arrayAllowed;

    /** determines if a value can be a pair of "from" and "to" values */
    private $rangeAllowed;

    /** @var string */
    private $arrayDelimiter = ',';

    /** @var string */
    private $rangeDelimiter = '..';

    /** @var bool */
    private $processed = false;

    private $result;

    /**
     * Gets a flag indicates whether a suitable processor has processed a value.
     *
     * @return bool
     */
    public function isProcessed()
    {
        return $this->processed;
    }

    /**
     * Sets a flag indicates whether a suitable processor has processed a value.
     *
     * @param bool $flag
     */
    public function setProcessed($flag)
    {
        $this->processed = $flag;
    }

    /**
     * Gets a delimiter that should be used to split a string to separate elements.
     *
     * @return string
     */
    public function getArrayDelimiter()
    {
        return $this->arrayDelimiter;
    }

    /**
     * Sets a delimiter that should be used to split a string to separate elements.
     *
     * @param string $delimiter
     */
    public function setArrayDelimiter($delimiter)
    {
        $this->arrayDelimiter = $delimiter;
    }

    /**
     * Gets a delimiter that should be used to split a string to a pair of "from" and "to" values.
     *
     * @return string
     */
    public function getRangeDelimiter()
    {
        return $this->rangeDelimiter;
    }

    /**
     * Sets a delimiter that should be used to split a string to a pair of "from" and "to" values.
     *
     * @param string $delimiter
     */
    public function setRangeDelimiter($delimiter)
    {
        $this->rangeDelimiter = $delimiter;
    }

    /**
     * @return mixed
     */
    public function getDataType()
    {
        return $this->dataType;
    }

    /**
     * @param mixed $dataType
     *
     * @return self
     */
    public function setDataType($dataType)
    {
        $this->dataType = $dataType;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getRequirement()
    {
        return $this->requirement;
    }

    /**
     * @param mixed $requirement
     *
     * @return self
     */
    public function setRequirement($requirement)
    {
        $this->requirement = $requirement;

        return $this;
    }

    /**
     * @return mixed
     */
    public function isArrayAllowed()
    {
        return $this->arrayAllowed;
    }

    /**
     * @param mixed $arrayAllowed
     *
     * @return self
     */
    public function setArrayAllowed($arrayAllowed)
    {
        $this->arrayAllowed = $arrayAllowed;

        return $this;
    }

    /**
     * @return mixed
     */
    public function isRangeAllowed()
    {
        return $this->rangeAllowed;
    }

    /**
     * @param mixed $rangeAllowed
     *
     * @return self
     */
    public function setRangeAllowed($rangeAllowed)
    {
        $this->rangeAllowed = $rangeAllowed;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * @param mixed $result
     *
     * @return self
     */
    public function setResult($result)
    {
        $this->result = $result;

        return $this;
    }
}
