<?php

namespace App\Bundle\RestBundle\Processor\Shared;

use App\Bundle\RestBundle\Processor\Context;
use App\Bundle\RestBundle\Util\CriteriaConnector;
use App\Bundle\RestBundle\Util\DoctrineHelper;
use Oro\Component\ChainProcessor\ContextInterface;
use Oro\Component\ChainProcessor\ProcessorInterface;

/**
 * Builds ORM QueryBuilder object that will be used to get a list of entities
 * based on the Criteria object.
 */
class BuildQuery implements ProcessorInterface
{
    /** @var DoctrineHelper */
    protected $doctrineHelper;

    /** @var CriteriaConnector */
    protected $criteriaConnector;

    /**
     * @param DoctrineHelper    $doctrineHelper
     * @param CriteriaConnector $criteriaConnector
     */
    public function __construct(DoctrineHelper $doctrineHelper, CriteriaConnector $criteriaConnector)
    {
        $this->doctrineHelper = $doctrineHelper;
        $this->criteriaConnector = $criteriaConnector;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContextInterface $context)
    {
        /** @var Context $context */

        if ($context->hasQuery()) {
            // a query is already built
            return;
        }

        $criteria = $context->getCriteria();
        if (null === $criteria) {
            // the criteria object does not exist
            return;
        }

        if (!$this->doctrineHelper->isManageableEntityClass($context->getClassName())) {
            // only manageable entities or resources based on manageable entities are supported
            return;
        }

        $query = $this->doctrineHelper->getEntityRepositoryForClass($context->getClassName())->createQueryBuilder('e');
        $this->criteriaConnector->applyCriteria($query, $criteria);

        $context->setQuery($query);
    }
}
