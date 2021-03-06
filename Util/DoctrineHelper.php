<?php

declare(strict_types=1);

namespace Videni\Bundle\RapidGraphQLBundle\Util;

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
     * Extracts the identifier values of the given entity.
     *
     * @param object $entity An entity object
     *
     * @return array
     */
    public function getEntityIdentifier($entity)
    {
        // check if we can use getId method to fast get the identifier
        if (method_exists($entity, 'getId')) {
            // This code doesn't support composite keys. See BAP-8835
            return ['id' => $entity->getId()];
        }

        return $this
            ->getEntityMetadata($entity)
            ->getIdentifierValues($entity);
    }

     /**
     * Extracts the single identifier value of the given entity.
     *
     * @param object $entity         An entity object
     * @param bool   $throwException Whether to throw exception in case the entity has several identifier fields
     *
     * @return mixed|null
     *
     * @throws \RuntimeException
     */
    public function getSingleEntityIdentifier($entity, $throwException = true)
    {
        $entityIdentifier = $this->getEntityIdentifier($entity);

        $result = null;
        if (count($entityIdentifier) > 1) {
            if ($throwException) {
                throw new \RuntimeException(
                    sprintf(
                        'Can\'t get single identifier for "%s" entity.',
                        $this->getEntityClass($entity)
                    )
                );
            }
        } else {
            $result = $entityIdentifier ? reset($entityIdentifier) : null;
        }

        return $result;
    }

    /**
     * Gets an array of identifier field names for the given entity or class.
     *
     * @param object|string $entityOrClass  An entity object, entity class name or entity proxy class name
     * @param bool          $throwException Whether to throw exception in case the entity is not manageable
     *
     * @return string[]
     *
     * @throws Exception\InvalidEntityException
     */
    public function getEntityIdentifierFieldNames($entityOrClass, $throwException = true)
    {
        $em = $this->getEntityMetadata($entityOrClass, $throwException);

        return null !== $em
            ? $em->getIdentifierFieldNames()
            : [];
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
     * Gets the ORM metadata descriptor for the given entity or class.
     *
     * @param object|string $entityOrClass  An entity object, entity class name or entity proxy class name
     * @param bool          $throwException Whether to throw exception in case the entity is not manageable
     *
     * @return ClassMetadata|null
     *
     * @throws Exception\NotManageableEntityException if the EntityManager was not found and $throwException is TRUE
     */
    public function getEntityMetadata($entityOrClass, $throwException = true)
    {
        return $this->getEntityMetadataForClass(
            $this->getEntityClass($entityOrClass),
            $throwException
        );
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

       /**
     * Gets the ORM metadata descriptor for target entity class of the given child association.
     *
     * @param string          $entityClass
     * @param string[]|string $associationPath
     *
     * @return ClassMetadata|null
     */
    public function findEntityMetadataByPath($entityClass, $associationPath)
    {
        $manager = $this->registry->getManagerForClass($entityClass);
        if (null === $manager) {
            return null;
        }

        $metadata = $manager->getClassMetadata($entityClass);
        if (null !== $metadata) {
            if (!is_array($associationPath)) {
                $associationPath = explode('.', $associationPath);
            }
            foreach ($associationPath as $associationName) {
                if (!$metadata->hasAssociation($associationName)) {
                    $metadata = null;
                    break;
                }
                $metadata = $manager->getClassMetadata($metadata->getAssociationTargetClass($associationName));
            }
        }

        return $metadata;
    }
}
