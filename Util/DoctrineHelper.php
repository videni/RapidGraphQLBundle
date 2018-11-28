<?php

declare(strict_types=1);

namespace Videni\Bundle\RestBundle\Util;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\Mapping\ClassMetadata;

class DoctrineHelper
{
     /** @var array */
    protected $manageableEntityClasses = [];

    /** @var ManagerRegistry */
    protected $registry;

    /**
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * Gets the EntityManager associated with the given entity or class.
     *
     * @param object|string $entityOrClass  An entity object, entity class name or entity proxy class name
     * @param bool          $throwException Whether to throw exception in case the entity is not manageable
     *
     * @return EntityManager|null
     *
     * @throws Exception\NotManageableEntityException if the EntityManager was not found and $throwException is TRUE
     */
    public function getEntityManager($entityOrClass, $throwException = true)
    {
        return $this->getEntityManagerForClass(
            $this->getEntityClass($entityOrClass),
            $throwException
        );
    }

     /**
     * Gets a real class name for an entity.
     *
     * @param object|string $entityOrClass An entity object, entity class name or entity proxy class name
     *
     * @return string
     */
    public function getEntityClass($entityOrClass)
    {
        if (is_object($entityOrClass)) {
            return ClassUtils::getClass($entityOrClass);
        }

        if (strpos($entityOrClass, ':') !== false) {
            list($namespaceAlias, $simpleClassName) = explode(':', $entityOrClass, 2);
            return $this->registry->getAliasNamespace($namespaceAlias) . '\\' . $simpleClassName;
        }

        return ClassUtils::getRealClass($entityOrClass);
    }

     /**
     * Gets the EntityManager associated with the given class.
     *
     * @param string $entityClass    The real class name of an entity
     * @param bool   $throwException Whether to throw exception in case the entity is not manageable
     *
     * @return EntityManager|null
     *
     * @throws Exception\NotManageableEntityException if the EntityManager was not found and $throwException is TRUE
     */
    public function getEntityManagerForClass($entityClass, $throwException = true)
    {
        $manager = $this->registry->getManagerForClass($entityClass);
        if (null === $manager && $throwException) {
            throw new Exception\NotManageableEntityException($entityClass);
        }

        return $manager;
    }

    /**
     * {@inheritdoc}
     */
    public function isManageableEntityClass($entityClass)
    {
        if (isset($this->manageableEntityClasses[$entityClass])) {
            return $this->manageableEntityClasses[$entityClass];
        }

        $isManageable = null !== $this->registry->getManagerForClass($entityClass);
        $this->manageableEntityClasses[$entityClass] = $isManageable;

        return $isManageable;
    }

      /**
     * Gets the repository for the given entity class.
     *
     * @param string $entityClass The real class name of an entity
     *
     * @return EntityRepository
     */
    public function getEntityRepositoryForClass($entityClass)
    {
        return $this
            ->getEntityManagerForClass($entityClass)
            ->getRepository($entityClass);
    }

      /**
     * Gets the ORM metadata descriptor for the given entity class.
     *
     * @param string $entityClass    The real class name of an entity
     * @param bool   $throwException Whether to throw exception in case the entity is not manageable
     *
     * @return ClassMetadata|null
     *
     * @throws Exception\NotManageableEntityException if the EntityManager was not found and $throwException is TRUE
     */
    public function getEntityMetadataForClass($entityClass, $throwException = true)
    {
        $manager = $this->registry->getManagerForClass($entityClass);
        if (null === $manager && $throwException) {
            throw new Exception\NotManageableEntityException($entityClass);
        }

        return null !== $manager
            ? $manager->getClassMetadata($entityClass)
            : null;
    }

      /**
     * Gets a list of all indexed associations
     *
     * @param ClassMetadata $metadata
     *
     * @return array [field name => target field data-type, ...]
     */
    public function getIndexedAssociations(ClassMetadata $metadata)
    {
        $relations = [];
        $fieldNames = $metadata->getAssociationNames();
        foreach ($fieldNames as $fieldName) {
            $targetMetadata = $this->getEntityMetadataForClass($metadata->getAssociationTargetClass($fieldName));
            $targetIdFieldNames = $targetMetadata->getIdentifierFieldNames();
            if (count($targetIdFieldNames) === 1) {
                $relations[$fieldName] = $targetMetadata->getTypeOfField(reset($targetIdFieldNames));
            }
        }

        return $relations;
    }
}
