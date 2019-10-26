<?php

namespace Videni\Bundle\RapidGraphQLBundle\Doctrine\ORM;

use Doctrine\ORM\QueryBuilder;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Paginator;
use Doctrine\Common\Persistence\ManagerRegistry;
use LogicException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepositoryInterface;

class ServiceEntityRepository extends EntityRepository implements ServiceEntityRepositoryInterface
{
    /**
     * @param string $entityClass The class name of the entity this repository manages
     */
    public function __construct(ManagerRegistry $registry, $entityClass)
    {
        $manager = $registry->getManagerForClass($entityClass);

        if ($manager === null) {
            throw new LogicException(sprintf(
                'Could not find the entity manager for class "%s". Check your Doctrine configuration to make sure it is configured to load this entity’s metadata.',
                $entityClass
            ));
        }

        parent::__construct($manager, $manager->getClassMetadata($entityClass));
    }
}
