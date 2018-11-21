<?php

namespace App\Bundle\RestBundle\Processor\Shared;

use Doctrine\ORM\EntityManagerInterface;
use App\Bundle\RestBundle\Processor\Context;
use App\Bundle\RestBundle\Util\DoctrineHelper;
use App\Bundle\RestBundle\Handler\DeleteHandler;
use Oro\Component\ChainProcessor\ContextInterface;
use Oro\Component\ChainProcessor\ProcessorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use App\Bundle\RestBundle\Handler\DeleteHandlerInterface;

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

        if (!$context->hasResult()) {
            // result deleted or not supported
            return;
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
