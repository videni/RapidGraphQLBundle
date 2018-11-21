<?php

namespace App\Bundle\RestBundle\Processor\Delete;

use Doctrine\ORM\EntityManagerInterface;
use App\Bundle\RestBundle\Processor\Shared\DeleteProcessor;
use App\Bundle\RestBundle\Handler\DeleteHandlerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Oro\Component\ChainProcessor\ContextInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Deletes an entity by DeleteHandler.
 */
class DeleteResource extends DeleteProcessor
{
    /**
     * {@inheritdoc}
     */
    public function process(ContextInterface $context)
    {
        /** @var Context $context */
        if (null === $context->getResult()) {
            throw new NotFoundHttpException(sprintf('The "%s" has not been found', $context->getMetadata()->getShortName()));
        }

        parent::process($context);

        $context->setResponseStatusCode(Response::HTTP_NO_CONTENT);
    }

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
