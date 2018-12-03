<?php

namespace Videni\Bundle\RestBundle\Form\DataTransformer;

use Videni\Bundle\RestBundle\Collection\IncludedEntityCollection;
use Videni\Bundle\RestBundle\Metadata\AssociationMetadata;
use Videni\Bundle\RestBundle\Util\DoctrineHelper;
use Videni\Bundle\RestBundle\Util\EntityLoader;
use Videni\Bundle\RestBundle\Util\EntityMapper;

/**
 * Transforms class name and identifier of an entity to an entity object.
 */
class EntityToIdTransformer extends AbstractEntityAssociationTransformer
{
    /** @var EntityMapper|null */
    protected $entityMapper;

    /** @var IncludedEntityCollection|null */
    protected $includedEntities;

    /**
     * @param DoctrineHelper                $doctrineHelper
     * @param EntityLoader                  $entityLoader
     * @param AssociationMetadata           $metadata
     * @param EntityMapper|null             $entityMapper
     * @param IncludedEntityCollection|null $includedEntities
     */
    public function __construct(
        DoctrineHelper $doctrineHelper,
        EntityLoader $entityLoader,
        AssociationMetadata $metadata,
        EntityMapper $entityMapper = null,
        IncludedEntityCollection $includedEntities = null
    ) {
        parent::__construct($doctrineHelper, $entityLoader, $metadata);
        $this->entityMapper = $entityMapper;
        $this->includedEntities = $includedEntities;
    }

    /**
     * @param string $entityClass
     * @param mixed  $entityId
     *
     * @return object
     */
    protected function getEntity($entityClass, $entityId)
    {
        $entity = $this->getIncludedEntity($entityClass, $entityId);
        if (null === $entity) {
            $resolvedEntityClass = $this->resolveEntityClass($entityClass);
            $entity = $this->loadEntity($resolvedEntityClass, $entityId);
            if (null !== $this->entityMapper && $resolvedEntityClass !== $entityClass) {
                $entity = $this->entityMapper->getModel($entity, $entityClass);
            }
        }

        return $entity;
    }

    /**
     * @param string $entityClass
     * @param mixed  $entityId
     *
     * @return object|null
     */
    protected function getIncludedEntity($entityClass, $entityId)
    {
        if (null === $this->includedEntities) {
            return null;
        }

        if ($this->includedEntities->isPrimaryEntity($entityClass, $entityId)) {
            return $this->includedEntities->getPrimaryEntity();
        }

        $entity = $this->includedEntities->get($entityClass, $entityId);
        if (null !== $this->entityMapper && null !== $entity) {
            $entity = $this->entityMapper->getModel($entity);
        }

        return $entity;
    }
}
