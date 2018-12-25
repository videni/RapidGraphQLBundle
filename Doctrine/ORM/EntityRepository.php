<?php

namespace Videni\Bundle\RestBundle\Doctrine\ORM;

use Doctrine\ORM\EntityRepository as BaseEntityRepository;
use Doctrine\ORM\QueryBuilder;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Paginator;

class EntityRepository extends BaseEntityRepository
{
     /**
     * {@inheritdoc}
     */
    public function add($resource): void
    {
        $this->_em->persist($resource);
        $this->_em->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function remove($resource): void
    {
        if (null !== $this->find($resource->getId())) {
            $this->_em->remove($resource);
            $this->_em->flush();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function createPaginator(array $criteria = [], array $sorting = []): iterable
    {
        $queryBuilder = $this->createQueryBuilder('o');

        $this->applyCriteria($queryBuilder, $criteria);
        $this->applySorting($queryBuilder, $sorting);

        return $this->getPaginator($queryBuilder);
    }

     /**
     * @param QueryBuilder $queryBuilder
     *
     * @return Pagerfanta
     */
    protected function getPaginator(QueryBuilder $queryBuilder): Pagerfanta
    {
        // Use output walkers option in DoctrineORMAdapter should be false as it affects performance greatly (see #3775)
        return new Paginator(new DoctrineORMAdapter($queryBuilder, false, false));
    }

    public function newQueryBuilderWithCriteria(array $criteria = [])
    {
       $qb = $this->createQueryBuilder('o');

       $this->applyCriteria($qb, $criteria);

       return $qb;
    }

     /**
     * @param QueryBuilder $queryBuilder
     * @param array $criteria
     */
    protected function applyCriteria(QueryBuilder $queryBuilder, array $criteria = []): void
    {
        foreach ($criteria as $property => $value) {
            $isStartedWithIdentity = $this->isStartedWithIdentity($property);
            if (!$isStartedWithIdentity && !in_array($property, array_merge($this->_class->getAssociationNames(), $this->_class->getFieldNames()), true)) {
                continue;
            }

            $name =  $isStartedWithIdentity ? $property: $this->getPropertyName($property);

            if (null === $value) {
                $queryBuilder->andWhere($queryBuilder->expr()->isNull($name));
            } elseif (is_array($value)) {
                $queryBuilder->andWhere($queryBuilder->expr()->in($name, $value));
            } elseif ('' !== $value) {
                $parameter = preg_replace('/[\.\(\)]/', '_', $property);  // replace '(', ')' ,'.'  with '_'
                $queryBuilder
                    ->andWhere($queryBuilder->expr()->eq($name, ':' . $parameter))
                    ->setParameter($parameter, $value)
                ;
            }
        }
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param array $sorting
     */
    protected function applySorting(QueryBuilder $queryBuilder, array $sorting = []): void
    {
        foreach ($sorting as $property => $order) {
            if (!in_array($property, array_merge($this->_class->getAssociationNames(), $this->_class->getFieldNames()), true)) {
                continue;
            }

            if (!empty($order)) {
                $queryBuilder->addOrderBy($this->getPropertyName($property), $order);
            }
        }
    }

    /**
     * @param string $name
     *
     * @return string
     */
    protected function getPropertyName(string $name): string
    {

        if (false === strpos($name, '.')) {
            return 'o' . '.' . $name;
        }

        return $name;
    }

    private function isStartedWithIdentity($name)
    {
        return 0 === strpos($name, 'IDENTITY', 0);
    }
}
