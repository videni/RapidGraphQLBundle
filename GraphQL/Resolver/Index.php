<?php

namespace Videni\Bundle\RapidGraphQLBundle\GraphQL\Resolver;

use Videni\Bundle\RapidGraphQLBundle\Definition\Argument;
use Pintushi\Bundle\GridBundle\Grid\Manager;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;
use Overblog\GraphQLBundle\Relay\Connection\ConnectionBuilder;
use Videni\Bundle\RapidGraphQLBundle\Controller\ControllerResolver;
use Videni\Bundle\RapidGraphQLBundle\Security\ResourceAccessCheckerInterface;
use Videni\Bundle\RapidGraphQLBundle\Relay\Connection\Output\Connection;
use Overblog\GraphQLBundle\Relay\Connection\Output\PageInfo;
use Overblog\GraphQLBundle\Relay\Connection\Output\Edge;

/**
 * @todo: Don't calculate total count if total is not requested.
 */
class Index extends AbstractResolver implements ResolverInterface
{
    private $gridManager;
    private $connectionBuilder;

    public function __construct(
        ResourceContextResolver $resourceContextResolver,
        ControllerResolver $controllerResolver,
        ControllerExecutor $controllerExecutor,
        ResourceAccessCheckerInterface $resourceAccessChecker,
        Manager $gridManager,
        ConnectionBuilder $connectionBuilder = null
    ) {
        parent::__construct($resourceContextResolver, $controllerResolver, $controllerExecutor, $resourceAccessChecker);

        $this->gridManager = $gridManager;
        $this->connectionBuilder = $connectionBuilder ?? new ConnectionBuilder();
    }

    public function __invoke(Argument $args, $operationName, $actionName)
    {
        $pagerParams = $args->getArrayCopy();

        $context = $this->resourceContextResolver->resolveResourceContext($operationName, $actionName);

        $this->checkPermission(null, $context->getAction(), $args);

        $grid = $this->gridManager->getGrid(
            $context->getGrid(),
            $pagerParams
        );

        /**
         * @var ResultsObject
         */
        $result = $grid->getData();
        $args->attributes->set('data', $result);

        if ($controller = $this->controllerResolver->getController($context)) {
            return $this->controllerExecutor->execute($controller, $args);
        }
        $items = $result->getData();
        $startOffset = $result->getCursor();
        $perPage = $result->getPerPage();

        $hasNextPage = count($items) == $result->getPerPage()+1;
        if ($hasNextPage) {
            $items = $result->isBackward() ? array_slice($items, 1) :  array_slice($items, 0, $perPage);
        }

        $edges = [];
        foreach ($items as $index => $value) {
            $cursor = $this->connectionBuilder->offsetToCursor($startOffset + 1 + $index);
            $edge = new Edge($cursor, $value);
            $edges[] = $edge;
        }

        $firstEdge = $edges[0] ?? null;
        $lastEdge = \end($edges);

        $total = $result->getTotalRecords();
        $hasPreviousPage = $result->isBackward() ? ($total - $startOffset)/$perPage > 0 : $startOffset/$perPage > 0;

        $pageInfo = new PageInfo(
            $firstEdge ? $firstEdge->getCursor(): null,
            $lastEdge? $lastEdge->getCursor(): null,
            $hasPreviousPage,
            $hasNextPage
        );

        $connection = new Connection($edges, $pageInfo);
        $connection->setTotalCount($total);

        return $connection;
    }
}
