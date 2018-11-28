<?php

namespace Videni\Bundle\RestBundle\Processor\BulkDelete;

use Doctrine\ORM\EntityManagerInterface;
use Videni\Bundle\RestBundle\Processor\Shared\DeleteProcessor;
use Videni\Bundle\RestBundle\Handler\DeleteHandlerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Oro\Component\ChainProcessor\ContextInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Deletes an entity by DeleteHandler.
 */
class BulkDeleteResource extends DeleteProcessor
{
    /**
     * {@inheritdoc}
     */
    protected function processDelete($data, DeleteHandlerInterface $handler, EntityManagerInterface $em)
    {
        /** @var DeleteListContext $context */

        if (!\is_array($data) && !$data instanceof \Traversable) {
            throw new \RuntimeException(
                \sprintf(
                    'The result property of the context should be array or Traversable, "%s" given.',
                    \is_object($data) ? \get_class($data) : \gettype($data)
                )
            );
        }

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
    }
}
