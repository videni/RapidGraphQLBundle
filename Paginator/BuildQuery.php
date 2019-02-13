<?php

namespace Videni\Bundle\RestBundle\Paginator;

use Videni\Bundle\RestBundle\Processor\Context;
use Videni\Bundle\RestBundle\Util\CriteriaConnector;
use Videni\Bundle\RestBundle\Util\DoctrineHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\QueryBuilder;
use Videni\Bundle\RestBundle\Config\Resource\ResourceConfig;
use Videni\Bundle\RestBundle\Factory\ParametersParserInterface;
use Symfony\Component\HttpFoundation\Request;
use Videni\Bundle\RestBundle\Collection\Criteria;
use Videni\Bundle\RestBundle\Context\ResourceContext;
use Videni\Bundle\RestBundle\Util\AclHelperInterface;

/**
 * Builds ORM QueryBuilder object that will be used to get a list of entities
 * based on the Criteria object.
 */
class BuildQuery
{
    /** @var DoctrineHelper */
    protected $doctrineHelper;

    /** @var CriteriaConnector */
    protected $criteriaConnector;

    protected $container;

    protected $parametersParser;

    private $filterValueAccessorFactory;

    private $aclHelper;

    /**
     * @param DoctrineHelper    $doctrineHelper
     * @param CriteriaConnector $criteriaConnector
     */
    public function __construct(
        DoctrineHelper $doctrineHelper,
        CriteriaConnector $criteriaConnector,
        ContainerInterface $container,
        ParametersParserInterface $parametersParser,
        AclHelperInterface $aclHelper = null
    ) {
        $this->doctrineHelper = $doctrineHelper;
        $this->criteriaConnector = $criteriaConnector;
        $this->container = $container;
        $this->parametersParser = $parametersParser;
        $this->aclHelper = $aclHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function build(Criteria $criteria, ResourceContext $context, Request $request)
    {
        $query = $this->resolveQueryBuilder(
            $request,
            $context
        );

        $this->criteriaConnector->applyCriteria($query, $criteria);

        return $query;
    }

    protected function resolveQueryBuilder(Request $request, ResourceContext $context)
    {
          /** @var ServiceConfig */
        $repositoryConfig = $context->getOperationConfig()->getRepository();

        $repositoryInstance = $this->container->get($repositoryConfig->getId());

        $query = null;

        if ($method = $repositoryConfig->getMethod()) {
            $arguments = $repositoryConfig->getArguments() ?? [];
            if (!is_array($arguments)) {
                $arguments = [$arguments];
            }

            $arguments = $this->parametersParser->parseRequestValues($arguments, $request);

            $query = $repositoryConfig->getSpreadArguments() ? $repositoryInstance->$method(...array_values($arguments)) : $repositoryInstance->$method($arguments);

            if (!$query instanceof QueryBuilder) {
                throw new \LogicException(sprintf(
                    'It must return an instance of %s method %s repository %s',
                    QueryBuilder::class,
                    $method,
                    $repositoryConfig->getId()
                ));
            }
        }

        if (null === $this->aclHelper) {
            return $query;
        }

        $query = $this->doctrineHelper->getEntityRepositoryForClass($context->getClassName())->createQueryBuilder('o');
        if ($context->getOperationConfig()->isAclEnabled()) {
            return $this->aclHelper->apply($query);
        }

        return $query;
    }
}
