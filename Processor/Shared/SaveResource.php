<?php

namespace App\Bundle\RestBundle\Processor\Shared;

use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use App\Bundle\RestBundle\Processor\SingleItemContext;
use Oro\Component\ChainProcessor\ContextInterface;
use Oro\Component\ChainProcessor\ProcessorInterface;
use Doctrine\Common\Persistence\ObjectManager as DoctrineObjectManager;
use Doctrine\Common\Util\ClassUtils;
use App\Bundle\RestBundle\Exception;
use Symfony\Component\HttpFoundation\Response;
use App\Bundle\RestBundle\Util\DoctrineHelper;

/**
 * Saves new ORM entity to the database and save its identifier into the context.
 */
class SaveResource implements ProcessorInterface
{
    private $doctrineHelper;

    public function __construct(DoctrineHelper $doctrineHelper)
    {
        $this->doctrineHelper = $doctrineHelper;
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

        // save entity id into the context
        if (null !==  $id = $entity->getId()) {
            $context->setId($id);
        }
    }
}
