<?php

namespace App\Bundle\RestBundle\Processor\Shared;

use App\Bundle\RestBundle\Processor\Context;
use App\Bundle\RestBundle\Util\CriteriaConnector;
use App\Bundle\RestBundle\Util\DoctrineHelper;
use Oro\Component\ChainProcessor\ContextInterface;
use Oro\Component\ChainProcessor\ProcessorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\QueryBuilder;
use App\Bundle\RestBundle\Config\Resource\ResourceConfig;
use App\Bundle\RestBundle\Factory\ParametersParserInterface;
use Symfony\Component\HttpFoundation\Request;

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

    protected $container;

    protected $parametersParser;

    /**
     * @param DoctrineHelper    $doctrineHelper
     * @param CriteriaConnector $criteriaConnector
     */
    public function __construct(
        DoctrineHelper $doctrineHelper,
        CriteriaConnector $criteriaConnector,
        ContainerInterface $container,
        ParametersParserInterface $parametersParser
    ) {
        $this->doctrineHelper = $doctrineHelper;
        $this->criteriaConnector = $criteriaConnector;
        $this->container = $container;
        $this->parametersParser = $parametersParser;
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

        $query = $this->createQueryBuilder($context->getRequest(), $context->getOperationName(), $context->getClassName(), $context->getResourceConfig());
        if ($query instanceof QueryBuilder) {
            $this->criteriaConnector->applyCriteria($query, $criteria);
            $context->setQuery($query);
        }
    }

    protected function createQueryBuilder(Request $request, $operationName, $entityClass, ResourceConfig $resourceConfig)
    {
          /** @var ServiceConfig */
        $repositoryConfig = $resourceConfig->getOperationAttribute($operationName, 'repository');

        $repositoryInstance = $this->container->get($repositoryConfig->getId());

        if ($method = $repositoryConfig->getMethod()) {
            $arguments = $repositoryConfig->getArguments() ?? [];
            if (!is_array($arguments)) {
                $arguments = [$arguments];
            }

            $arguments = array_values($this->parametersParser->parseRequestValues($arguments, $request));

            return $repositoryInstance->$method(...$arguments);
        }

        return $query = $this->doctrineHelper->getEntityRepositoryForClass($entityClass)->createQueryBuilder('o');
    }
}
