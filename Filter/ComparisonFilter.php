<?php

namespace App\Bundle\RestBundle\Filter;

use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Expr\Comparison;
use Doctrine\Common\Collections\Expr\Expression;
use Doctrine\Common\Collections\Expr\Value;
use App\Bundle\RestBundle\Exception\InvalidFilterOperatorException;
use App\Bundle\RestBundle\Model\Range;

/**
 * A filter that can be used to filter data by a field value.
 * Supported comparison types:
 * * equal to
 * * not equal to
 * * less than
 * * less than or equal to
 * * greater than
 * * greater than or equal to
 * * exists (is not null)
 * * not exists (is null)
 * * not equal to or is null
 * * contains
 * * not contains
 * * starts with
 * * not starts with
 * * ends with
 * * not ends with
 * Also the field value can be:
 * * an array, in this case IN expression will be used
 * * an instance of Range class, in this case BETWEEN expression will be used
 */
class ComparisonFilter extends StandaloneFilter implements FieldAwareFilterInterface
{
    /** @var string "not equal to" operator */
    public const NEQ = 'neq';
    /** @var string "less than" operator */
    public const LT = 'lt';
    /** @var string "less than or equal to" operator */
    public const LTE = 'lte';
    /** @var string "greater than" operator */
    public const GT = 'gt';
    /** @var string "greater than or equal to" operator */
    public const GTE = 'gte';
    /** @var string "exists" operator, value is true = EXISTS (IS NOT NULL), value is false = NOT EXISTS (IS NULL) */
    public const EXISTS = 'exists';
    /** @var string "not equal to or IS NULL" operator */
    public const NEQ_OR_NULL = 'neq_or_null';
    /** @var string "contains" (LIKE %value%) operator */
    public const CONTAINS = 'contains';
    /** @var string "not contains" (NOT LIKE %value%) operator */
    public const NOT_CONTAINS = 'not_contains';
    /** @var string "starts with" (LIKE value%) operator */
    public const STARTS_WITH = 'starts_with';
    /** @var string "not starts with" (NOT LIKE value%) operator */
    public const NOT_STARTS_WITH = 'not_starts_with';
    /** @var string "ends with" (LIKE %value) operator */
    public const ENDS_WITH = 'ends_with';
    /** @var string "not ends with" (NOT LIKE %value) operator */
    public const NOT_ENDS_WITH = 'not_ends_with';

    /** @var string */
    protected $field;

    /** @var bool */
    private $caseInsensitive = false;

    /** @var callable|null */
    private $valueTransformer;

    /**
     * Gets a field by which the data is filtered.
     *
     * @return string|null
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * {@inheritdoc}
     */
    public function setField($field)
    {
        $this->field = $field;
    }

    /**
     * {@inheritdoc}
     */
    public function isArrayAllowed($operator = null)
    {
        return
            parent::isArrayAllowed($operator)
            && (null === $operator || \in_array($operator, [self::EQ, self::NEQ], true));
    }

    /**
     * {@inheritdoc}
     */
    public function isRangeAllowed($operator = null)
    {
        return
            parent::isRangeAllowed($operator)
            && (null === $operator || \in_array($operator, [self::EQ, self::NEQ], true));
    }

    /**
     * Sets a value that indicates whether case-insensitive comparison should be used.
     *
     * @param bool $caseInsensitive
     */
    public function setCaseInsensitive(bool $caseInsensitive)
    {
        $this->caseInsensitive = $caseInsensitive;
    }

    /**
     * Sets a callable that should be used to transform the filter value.
     *
     * @param callable $valueTransformer
     */
    public function setValueTransformer($valueTransformer)
    {
        $this->valueTransformer = $valueTransformer;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(Criteria $criteria, FilterValue $value = null)
    {
        $expr = $this->createExpression($value);
        if (null !== $expr) {
            $criteria->andWhere($expr);
        }
    }

    /**
     * Creates an expression that can be used to in WHERE statement to filter data by this filter.
     *
     * @param FilterValue|null $value
     *
     * @return Expression|null
     */
    protected function createExpression(FilterValue $value = null)
    {
        return null !== $value
            ? $this->buildExpression($this->field, $value->getPath(), $value->getOperator(), $value->getValue())
            : null;
    }

    /**
     * Creates the Expression object that can be used to filter data using the Criteria object.
     *
     * @param string      $field
     * @param string      $path
     * @param string|null $operator
     * @param mixed       $value
     *
     * @return Expression
     *
     * @throws \InvalidArgumentException
     * @throws InvalidFilterOperatorException
     */
    protected function buildExpression($field, $path, $operator, $value)
    {
        if (!$field) {
            throw new \InvalidArgumentException('The field must not be empty.');
        }
        if (null === $value) {
            throw new \InvalidArgumentException(\sprintf('The value must not be NULL. Field: "%s".', $field));
        }

        if (null === $operator) {
            $operator = self::EQ;
        }
        if (\in_array($operator, $this->operators, true)) {
            $expr = $this->doBuildExpression($field, $path, $operator, $value);
            if (null !== $expr) {
                return $expr;
            }
        }

        throw new InvalidFilterOperatorException($operator);
    }

    /**
     * @param string $field
     * @param string $path
     * @param string $operator
     * @param mixed  $value
     *
     * @return Expression|null
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function doBuildExpression($field, $path, $operator, $value)
    {
        switch ($operator) {
            case self::EQ:
                return $this->buildEqualToExpression($field, $value);
            case self::NEQ:
                return $this->buildNotEqualToExpression($field, $value);
            case self::GT:
                return $this->buildComparisonExpression($field, Comparison::GT, $value);
            case self::LT:
                return $this->buildComparisonExpression($field, Comparison::LT, $value);
            case self::GTE:
                return $this->buildComparisonExpression($field, Comparison::GTE, $value);
            case self::LTE:
                return $this->buildComparisonExpression($field, Comparison::LTE, $value);
            case self::EXISTS:
                return $this->buildComparisonExpression($field, 'EXISTS', $value);
            case self::NEQ_OR_NULL:
                return $this->buildComparisonExpression($field, 'NEQ_OR_NULL', $value);
            case self::CONTAINS:
                return $this->buildComparisonExpression($field, Comparison::CONTAINS, $value);
            case self::NOT_CONTAINS:
                return $this->buildComparisonExpression($field, 'NOT_CONTAINS', $value);
            case self::STARTS_WITH:
                return $this->buildComparisonExpression($field, Comparison::STARTS_WITH, $value);
            case self::NOT_STARTS_WITH:
                return $this->buildComparisonExpression($field, 'NOT_STARTS_WITH', $value);
            case self::ENDS_WITH:
                return $this->buildComparisonExpression($field, Comparison::ENDS_WITH, $value);
            case self::NOT_ENDS_WITH:
                return $this->buildComparisonExpression($field, 'NOT_ENDS_WITH', $value);
            default:
                return null;
        }
    }

    /**
     * @param string $field
     * @param mixed  $value
     *
     * @return Expression
     */
    protected function buildEqualToExpression($field, $value)
    {
        if (\is_array($value)) {
            return $this->buildComparisonExpression($field, Comparison::IN, $value);
        }
        if ($value instanceof Range) {
            // expression: (field >= fromValue AND field <= toValue)
            // this expression equals to "field BETWEEN fromValue AND toValue",
            // but Criteria object does not support BETWEEN expression
            return Criteria::expr()->andX(
                $this->buildComparisonExpression($field, Comparison::GTE, $value->getFromValue()),
                $this->buildComparisonExpression($field, Comparison::LTE, $value->getToValue())
            );
        }

        return $this->buildComparisonExpression($field, Comparison::EQ, $value);
    }

    /**
     * @param string $field
     * @param mixed  $value
     *
     * @return Expression
     */
    protected function buildNotEqualToExpression($field, $value)
    {
        if (\is_array($value)) {
            return $this->buildComparisonExpression($field, Comparison::NIN, $value);
        }
        if ($value instanceof Range) {
            // expression: (field < fromValue OR field > toValue)
            // this expression equals to "NOT field BETWEEN fromValue AND toValue",
            // but Criteria object does not support NOT and BETWEEN expressions
            return Criteria::expr()->orX(
                $this->buildComparisonExpression($field, Comparison::LT, $value->getFromValue()),
                $this->buildComparisonExpression($field, Comparison::GT, $value->getToValue())
            );
        }

        return $this->buildComparisonExpression($field, Comparison::NEQ, $value);
    }

    /**
     * @param string $field
     * @param string $operator
     * @param mixed  $value
     *
     * @return Comparison
     */
    protected function buildComparisonExpression($field, $operator, $value)
    {
        if ($this->caseInsensitive) {
            $operator .= '/i';
            $value = $this->transformValue($value, 'strtolower');
        } elseif (null !== $this->valueTransformer) {
            $value = $this->transformValue($value, $this->valueTransformer);
        }

        return new Comparison($field, $operator, new Value($value));
    }

    /**
     * @param mixed    $value
     * @param callable $transformer
     *
     * @return mixed
     */
    private function transformValue($value, $transformer)
    {
        if (null !== $value) {
            if (\is_array($value)) {
                $value = \array_map($transformer, $value);
            } else {
                $value = \call_user_func($transformer, $value);
            }
        }

        return $value;
    }
}
