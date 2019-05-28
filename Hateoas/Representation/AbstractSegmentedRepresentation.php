<?php

declare(strict_types=1);

namespace Videni\Bundle\RestBundle\Hateoas\Representation;

use JMS\Serializer\Annotation as Serializer;
use Hateoas\Representation\RouteAwareRepresentation;

/**
 * @Serializer\ExclusionPolicy("all")
 */
abstract class AbstractSegmentedRepresentation extends RouteAwareRepresentation
{
    /**
     * @Serializer\Expose
     * @Serializer\Type("integer")
     * @Serializer\XmlAttribute
     *
     * @var int
     */
    private $limit;

    /**
     * @var string
     */
    private $limitParameterName;

    /**
     * @param mixed $inline
     */
    public function __construct(
        $inline,
        string $route,
        array $parameters = [],
        int $limit,
        ?string $limitParameterName = null,
        bool $absolute = false
    ) {
        parent::__construct($inline, $route, $parameters, $absolute);

        $this->limit               = $limit;
        $this->limitParameterName  = $limitParameterName ?: 'limit';
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    /**
     * @param  null  $limit
     *
     * @return array
     */
    public function getParameters(?int $limit = null): array
    {
        $parameters = parent::getParameters();

        $parameters[$this->limitParameterName] = $limit ?? $this->getLimit();

        return $parameters;
    }

    public function getLimitParameterName(): string
    {
        return $this->limitParameterName;
    }

    protected function moveParameterToEnd(array &$parameters, string $key): void
    {
        if (! array_key_exists($key, $parameters)) {
            return;
        }

        $value = $parameters[$key];
        unset($parameters[$key]);
        $parameters[$key] = $value;
    }
}
