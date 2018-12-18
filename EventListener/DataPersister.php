<?php

namespace Videni\Bundle\RestBundle\EventListener;

use Videni\Bundle\RestBundle\Util\DoctrineHelper;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\Common\Persistence\ObjectManager as DoctrineObjectManager;

class DataPersister
{
    public function __construct(DoctrineHelper $doctrineHelper)
    {
        $this->doctrineHelper = $doctrineHelper;
    }

    protected function persist($data)
    {
        $em = $this->doctrineHelper->getEntityManager($entity, false);
        if (!$em) {
            // only manageable entities are supported
            return;
        }

        $em->persist($entity);
        try {
            $em->flush();
        } catch (UniqueConstraintViolationException $e) {
            throw $e;
        }
    }

    protected function delete($data)
    {
        $em = $this->doctrineHelper->getEntityManager($entity, false);
        if (!$em) {
            // only manageable entities are supported
            return;
        }

        if (\is_array($data) || $data instanceof \Traversable) {
            $em->getConnection()->beginTransaction();
            try {
                foreach ($data as $entity) {
                    $handler->processDelete($entity, $em);
                }
                $em->getConnection()->commit();
            } catch (\Exception $e) {
                $em->getConnection()->rollBack();

                throw $e;
            }
        } else {
            $em->remove($data);
        }
    }
}
