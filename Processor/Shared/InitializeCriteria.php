<?php

namespace App\Bundle\RestBundle\Processor\Shared;

use App\Bundle\RestBundle\Collection\Criteria;
use App\Bundle\RestBundle\Processor\Context;
use App\Bundle\RestBundle\Util\DoctrineHelper;
use App\Bundle\RestBundle\ORM\EntityClassResolver;
use Oro\Component\ChainProcessor\ContextInterface;
use Oro\Component\ChainProcessor\ProcessorInterface;

/**
 * Checks whether the Criteria object exists in the context and adds it if not.
 */
class InitializeCriteria implements ProcessorInterface
{
    /** @var DoctrineHelper */
    protected $doctrineHelper;

    /** @var EntityClassResolver */
    protected $entityClassResolver;

    /**
     * @param DoctrineHelper      $doctrineHelper
     * @param EntityClassResolver $entityClassResolver
     */
    public function __construct(DoctrineHelper $doctrineHelper, EntityClassResolver $entityClassResolver)
    {
        $this->doctrineHelper = $doctrineHelper;
        $this->entityClassResolver = $entityClassResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContextInterface $context)
    {
        /** @var Context $context */

        if ($context->hasResult()) {
            // data already exist
            return;
        }

        if ($context->getCriteria()) {
            // the criteria object is already initialized
            return;
        }

        $entityClass = $this->doctrineHelper->getManageableEntityClass(
            $context->getClassName(),
            $context->getConfig()
        );
        if (!$entityClass) {
            // only manageable entities or resources based on manageable entities are supported
            return;
        }

        $context->setCriteria(new Criteria($this->entityClassResolver));
    }
}
