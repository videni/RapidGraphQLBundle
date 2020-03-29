<?php

namespace Videni\Bundle\RapidGraphQLBundle\GraphQL\Resolver;

use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Videni\Bundle\RapidGraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;
use Pintushi\Bundle\GridBundle\Grid\Manager;
use Overblog\GraphQLBundle\Relay\Connection\ConnectionBuilder;
use Videni\Bundle\RapidGraphQLBundle\Util\DoctrineHelper;
use Doctrine\ORM\Mapping\MappingException;
use Pintushi\Bundle\GridBundle\Datasource\Orm\OrmDatasource;

class Relation implements ResolverInterface
{
    use ConnectionTrait {
        ConnectionTrait::__construct as private connectionTraitConstruct;
    }

    private $doctrineHelper;

    public function __construct(
        Manager $gridManager,
        DoctrineHelper $doctrineHelper,
        ConnectionBuilder $connectionBuilder = null
    ) {
        $this->connectionTraitConstruct($gridManager, $connectionBuilder ?? new ConnectionBuilder());
        $this->doctrineHelper = $doctrineHelper;
    }

    public function __invoke($value, Argument $args, $gridName, $field)
    {
        $entityMetadata = $this->doctrineHelper->getEntityMetadata($value);
        if(!$entityMetadata->hasAssociation($field)) {
            throw MappingException::mappingNotFound($entityMetadata->getName(), $field);
        }
        $association = $entityMetadata->getAssociationMapping($field);
        if(!in_array($association['type'], [ClassMetadataInfo::ONE_TO_MANY, ClassMetadataInfo::MANY_TO_MANY])) {
            throw new \LogicException('Relation resolve only be used for one-to-many or many-to-many associations.');
        }
        $grid = $this->getGrid($args, $gridName);
        $dataSource = $grid->getDataSource();
        if (!$dataSource instanceof OrmDatasource) {
            throw new \RuntimeException(sprintf('Datasource %s is not supported', get_class($dataSource)));
        }

        $qb = $grid->getDataSource()->getQueryBuilder();
        $alias = $qb->getRootAlias();
        $mappedBy = $association['mappedBy'];
        if($association['type'] === ClassMetadataInfo::ONE_TO_MANY) {
            $qb
                ->andWhere(sprintf('IDENTITY(%s.%s)=:%2$s' , $alias, $mappedBy))
                ->setParameter(":$mappedBy", $this->doctrineHelper->getSingleEntityIdentifier($value));
        }
        if ($association['type'] === ClassMetadataInfo::MANY_TO_MANY) {
            $qb
                ->innerJoin("$alias.$mappedBy", "$mappedBy")
                ->andWhere(sprintf('IDENTITY(%s)=:%1$s', $mappedBy))
                ->setParameter(":$mappedBy", $this->doctrineHelper->getSingleEntityIdentifier($value));
                ;
        }

        /**
         * @var ResultsObject
         */
        $result = $grid->getData();

        return $this->convertResultsObjectToConnection($result);
    }
}
