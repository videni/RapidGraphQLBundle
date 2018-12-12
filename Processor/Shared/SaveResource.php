<?php

namespace Videni\Bundle\RestBundle\Processor\Shared;

use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Videni\Bundle\RestBundle\Processor\SingleItemContext;
use Oro\Component\ChainProcessor\ContextInterface;
use Oro\Component\ChainProcessor\ProcessorInterface;
use Doctrine\Common\Persistence\ObjectManager as DoctrineObjectManager;
use Doctrine\Common\Util\ClassUtils;
use Videni\Bundle\RestBundle\Exception;
use Symfony\Component\HttpFoundation\Response;
use Videni\Bundle\RestBundle\Util\DoctrineHelper;
use Videni\Bundle\RestBundle\Event\EventDispatcher;
use Videni\Bundle\RestBundle\Operation\ActionTypes;

/**
 * Saves new ORM entity to the database and save its identifier into the context.
 */
class SaveResource implements ProcessorInterface
{
    private $doctrineHelper;

    private $eventDispatcher;

    public function __construct(DoctrineHelper $doctrineHelper, EventDispatcher $eventDispatcher)
    {
        $this->doctrineHelper = $doctrineHelper;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContextInterface $context)
    {
        /** @var SingleItemContext $context */
        $entity = $context->getResult();
        if (!is_object($entity)) {
            // entity does not exist
            return;
        }

        $em = $this->doctrineHelper->getEntityManager($entity, false);
        if (!$em) {
            // only manageable entities are supported
            return;
        }

        if (null === $entity->getId()) {
            $context->setResponseStatusCode(Response::HTTP_CREATED);
        }
        $em->persist($entity);
        try {
            $em->flush();
        } catch (UniqueConstraintViolationException $e) {
            throw $e;
        }

        $this->eventDispatcher->dispatchPostEvent($context->getAction(), $context->getResourceConfig(), $entity);

        // save entity id into the context
        if (null !==  $id = $entity->getId()) {
            $context->setId($id);
        }
    }
}
