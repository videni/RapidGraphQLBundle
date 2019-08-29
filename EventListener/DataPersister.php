<?php

namespace Videni\Bundle\RestBundle\EventListener;

use Videni\Bundle\RestBundle\Util\DoctrineHelper;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\Common\Persistence\ObjectManager as DoctrineObjectManager;
use Videni\Bundle\RestBundle\Exception\DeleteHandlingException;

class DataPersister
{
    protected $doctrineHelper;

    public function __construct(DoctrineHelper $doctrineHelper)
    {
        $this->doctrineHelper = $doctrineHelper;
    }

    public function persist($data)
    {
        if(null === $data) {
            return;
        }

        $em = $this->doctrineHelper->getEntityManager($data, false);
        if (!$em) {
            // only manageable entities are supported
            return;
        }

        $em->persist($data);
        try {
            $em->flush();
        } catch (UniqueConstraintViolationException $e) {
            throw $e;
        }
    }

    public function remove($data)
    {
        if(null === $data) {
            return;
        }

        $em = $this->doctrineHelper->getEntityManager($data, false);
        if (!$em) {
            // only manageable entities are supported
            return;
        }

        if (\is_array($data) || $data instanceof \Traversable) {
            $em->getConnection()->beginTransaction();
            try {
                foreach ($data as $entity) {
                    $em->remove($entity);
                }
                $em->getConnection()->commit();
            } catch (\Exception $e) {
                $em->getConnection()->rollBack();

                throw $e;
            }
        } else {
            $em->remove($data);
        }
        try {
            $em->flush();
        } catch(\Exception $exception) {
            throw new DeleteHandlingException();
        }
    }
}
