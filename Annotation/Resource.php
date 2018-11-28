<?php

declare(strict_types=1);

namespace Videni\Bundle\RestBundle\Annotation;

use Videni\Bundle\RestBundle\Exception\InvalidArgumentException;

/**
 * Resource annotation.
 *
 * @Annotation
 * @Target({"CLASS"})
 * @Attributes(
 *     @Attribute("accessControl", type="string"),
 *     @Attribute("accessControlMessage", type="string"),
 *     @Attribute("attributes", type="array"),
 *     @Attribute("denormalizationContext", type="array"),
 *     @Attribute("operations", type="array"),
 *     @Attribute("description", type="string"),
 *     @Attribute("maximumItemsPerPage", type="int"),
 *     @Attribute("normalizationContext", type="array"),
 *     @Attribute("paginationItemsPerPage", type="int"),
 *     @Attribute("routePrefix", type="string"),
 *     @Attribute("validationGroups", type="mixed")
 * )
 */
final class Resource
{
    use AttributesHydratorTrait;

      /**
     * @var array
     */
    public $operations;

    /**
     * @var string
     */
    public $description;

    /**
     * @var string
     */
    private $accessControl;

    /**
     * @var string
     */
    private $accessControlMessage;

    /**
     * @var array
     */
    private $denormalizationContext;


    /**
     * @var int
     */
    private $maximumItemsPerPage;

    /**
     * @var array
     */
    private $normalizationContext;

    /**
     *
     * @var bool
     */
    private $paginationClientEnabled;

    /**
     * @var bool
     */
    private $paginationClientItemsPerPage;

    /**
     * @var bool
     */
    private $paginationEnabled;

    /**
     * @var int
     */
    private $paginationItemsPerPage;

    /**
     * @var string
     */
    private $routePrefix;

    /**
     * @var mixed
     */
    private $validationGroups;

    /**
     * @throws InvalidArgumentException
     */
    public function __construct(array $values = [])
    {
        $this->hydrateAttributes($values);
    }
}
