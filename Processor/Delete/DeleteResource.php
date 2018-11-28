<?php

namespace Videni\Bundle\RestBundle\Processor\Delete;

use Doctrine\ORM\EntityManagerInterface;
use Videni\Bundle\RestBundle\Processor\Shared\DeleteProcessor;
use Videni\Bundle\RestBundle\Handler\DeleteHandlerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Oro\Component\ChainProcessor\ContextInterface;

/**
 * Deletes an entity by DeleteHandler.
 */
class DeleteResource extends DeleteProcessor
{
    /**
     * {@inheritdoc}
     */
    protected function processDelete($data, DeleteHandlerInterface $handler, EntityManagerInterface $em)
    {
        if (!\is_object($data)) {
            throw new \RuntimeException(
                \sprintf(
                    'The result property of the context should be an object, "%s" given.',
                    \is_object($data) ? \get_class($data) : \gettype($data)
                )
            );
        }

        $handler->processDelete($data, $em);
    }
}
