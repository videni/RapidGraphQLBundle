<?php

declare(strict_types=1);

namespace Videni\Bundle\RestBundle\Hateoas\Factory;

use Hateoas\Configuration\Route;
use Hateoas\Model\Link;
use Hateoas\Serializer\ExclusionManager;
use JMS\Serializer\SerializationContext;
use Metadata\MetadataFactoryInterface;
use Hateoas\Factory\LinkFactory;
use Hateoas\Factory\LinksFactory as BaseLinksFactory;

/**
 * Allow to add dynamic relations via special method getRelations
 */
class LinksFactory extends BaseLinksFactory
{
    /**
     * @var LinkFactory
     */
    private $linkFactory;

    /**
     * @var ExclusionManager
     */
    private $exclusionManager;

    /**
     * @var MetadataFactoryInterface
     */
    private $metadataFactory;

    public function __construct(
        MetadataFactoryInterface $metadataFactory,
        LinkFactory $linkFactory,
        ExclusionManager $exclusionManager
    ) {
        $this->linkFactory = $linkFactory;
        $this->exclusionManager = $exclusionManager;
        $this->metadataFactory = $metadataFactory;
    }

    /**
     * @return Link[]
     */
    public function create(object $object, SerializationContext $context): array
    {
        $relations = [];
        //A hack way to get HATEOAS relations
        if (method_exists($object, 'getHateoasRelations')) {
            $relations = $object->getHateoasRelations($object, $context);
        }

        $links = [];
        if (null !== ($classMetadata = $this->metadataFactory->getMetadataForClass(get_class($object)))) {
            $relations = $classMetadata->getRelations() + $relations;
            foreach ($relations as $relation) {
                if ($this->exclusionManager->shouldSkipLink($object, $relation, $context)) {
                    continue;
                }
                $links[] = $this->linkFactory->createLink($object, $relation, $context);
            }
        }

        return $links;
    }
}
