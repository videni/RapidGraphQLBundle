<?php

declare(strict_types=1);

namespace Videni\Bundle\RestBundle\Provider\ResourceProvider;

use Symfony\Component\HttpFoundation\Request;
use Pagerfanta\Pagerfanta;
use Hateoas\Representation\Factory\PagerfantaFactory;
use Hateoas\Configuration\Route;
use Videni\Bundle\RestBundle\Context\ResourceContext;
use Videni\Bundle\RestBundle\Operation\ActionTypes;
use Pintushi\Bundle\GridBundle\Grid\Common\ResultsObject;
use Pintushi\Bundle\GridBundle\Grid\Manager;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Videni\Bundle\RestBundle\Factory\ParametersParserInterface;
use Doctrine\ORM\EntityRepository;
use Pintushi\Bundle\GridBundle\Datasource\Orm\OrmDatasource;
use Pintushi\Bundle\GridBundle\Datasource\ArrayDatasource\ArrayDatasource;
use Videni\Bundle\RestBundle\Config\Resource\Service;

/**
 *  Set default method and arguments of resource provider for index action, this will
 *  also override query builder from grid.
 */
class CollectionResourceProvider extends AbstractResourceProvider
{
    private $gridManager;

    private $pagerfantaRepresentationFactory;

    public function __construct(
        ContainerInterface $container,
        ParametersParserInterface $parametersParser,
        PagerfantaFactory $pagerfantaRepresentationFactory,
        Manager $gridManager
    ) {
        parent::__construct($container, $parametersParser);

        $this->pagerfantaRepresentationFactory = $pagerfantaRepresentationFactory;
        $this->gridManager = $gridManager;
    }

    public function supports(ResourceContext $context, Request $request)
    {
      return in_array($context->getAction(), [ActionTypes::INDEX]);
    }

    public function getResource(ResourceContext $context, Request $request)
    {
        if (!$this->supports($context, $request)) {
            return;
        }

        $gridName = $context->getGrid();
        if (!$gridName) {
            return;
        }

        $grid = $this->gridManager->getGrid(
            $gridName,
            array_merge(
                $request->request->all(),
                $request->query->all()
            )
        );

        $qb = parent::getResource($context, $request);
        if ($grid->getDatasource() instanceof OrmDatasource) {
            if (!$qb instanceof QueryBuilder) {
                throw new \LogicException(
                    sprintf(
                        'Resource provider for resource %s  operation %s must return %s',
                        $context->getClassName(),
                        $context->getOperationName(),
                        QueryBuilder::class
                    )
                );
            }
            $grid->getDatasource()->setQueryBuilder($qb);
        } else if($grid->getDatasource() instanceof ArrayDatasource) {
            if ($qb instanceof QueryBuilder) {
                $grid->getDatasource()->setArraySource($qb->getQuery()->getArrayResult());
            } elseif(is_array($qb)) {
                $grid->getDatasource()->setArraySource($qb);
            }
        }

        $route = new Route(
            $request->attributes->get('_route'),
            array_merge($request->attributes->get('_route_params'), $request->query->all())
        );

        /** @var ResultsObject */
        $resultsObject = $grid->getData();

        return $this->pagerfantaRepresentationFactory->createRepresentation($resultsObject->getData(), $route);
    }

    /**
     * Set createQueryBuilder as default method
     */
    protected function getMethod($providerInstance, Service $providerConfig): string
    {
        $method =  $providerConfig->getMethod();
        if (!$method && $providerInstance instanceof EntityRepository) {
            $method = 'createQueryBuilder';
        }

        return  $method;
    }


    /**
     * $repository->createQueryBuilder('o')
     */
    protected function getArguments(Request $request, Service $providerConfig): array
    {
        if (!$providerConfig->getMethod()) {
            return ['o'];
        }

        return parent::getArguments($request, $providerConfig);
    }
}
