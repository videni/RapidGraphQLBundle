<?php

namespace Videni\Bundle\RestBundle\Processor\Shared;

use Doctrine\ORM\EntityManagerInterface;
use Videni\Bundle\RestBundle\Processor\Context;
use Videni\Bundle\RestBundle\Util\DoctrineHelper;
use Videni\Bundle\RestBundle\Handler\DeleteHandler;
use Oro\Component\ChainProcessor\ContextInterface;
use Oro\Component\ChainProcessor\ProcessorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Videni\Bundle\RestBundle\Handler\DeleteHandlerInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * The base class for processors that deletes entities by DeleteHandler.
 */
abstract class DeleteProcessor implements ProcessorInterface
{
    /** @var DoctrineHelper */
    protected $doctrineHelper;

    /** @var ContainerInterface */
    protected $deleteHandler;

    /**
     * @param DoctrineHelper     $doctrineHelper
     * @param ContainerInterface $container
     */
    public function __construct(DoctrineHelper $doctrineHelper, DeleteHandlerInterface $deleteHandler)
    {
        $this->doctrineHelper = $doctrineHelper;
        $this->deleteHandler = $deleteHandler;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContextInterface $context)
    {
        /** @var Context $context */
        if (null === $context->getResult()) {
            throw new NotFoundHttpException(sprintf('The "%s" has not been found', $context->getMetadata()->getShortName()));
        }

        $className = $context->getClassName();

        $this->processDelete(
            $context->getResult(),
            $this->deleteHandler,
            $this->doctrineHelper->getEntityManagerForClass($className)
        );

        $context->removeResult();
    }


    /**
     * Deletes entity(es) stored in the given result property of the context using the delete handler
     *
     * @param mixed                  $data
     * @param DeleteHandler          $handler
     * @param EntityManagerInterface $em
     */
    abstract protected function processDelete($data, DeleteHandlerInterface $handler, EntityManagerInterface $em);
}
