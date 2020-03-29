<?php

namespace Videni\Bundle\RapidGraphQLBundle\GraphQL\Resolver;

use Videni\Bundle\RapidGraphQLBundle\Definition\Argument;
use Pintushi\Bundle\GridBundle\Grid\Manager;
use Overblog\GraphQLBundle\Relay\Connection\ConnectionBuilder;
use Videni\Bundle\RapidGraphQLBundle\Relay\Connection\Output\Connection;
use Overblog\GraphQLBundle\Relay\Connection\Output\PageInfo;
use Overblog\GraphQLBundle\Relay\Connection\Output\Edge;
use Pintushi\Bundle\GridBundle\Grid\Common\ResultsObject;
use Pintushi\Bundle\GridBundle\Grid\GridInterface;

/**
 * @todo: Don't calculate total count if total is not requested.
 */
trait ConnectionTrait
{
    private $gridManager;
    private $connectionBuilder;

    public function __construct(
        Manager $gridManager,
        ConnectionBuilder $connectionBuilder
    ) {
        $this->gridManager = $gridManager;
        $this->connectionBuilder = $connectionBuilder;
    }

    public function getConnection(Argument $args, $gridName)
    {
        $grid = $this->getGrid($args, $gridName);
        /**
         * @var ResultsObject
         */
        $result = $grid->getData();

        return $this->convertResultsObjectToConnection($result);
    }

    /**
     *
     * @param Argument $args
     * @param string $gridName
     * @return GridInterface
     */
    public function getGrid(Argument $args, $gridName)
    {
        $pagerParams = $args->getArrayCopy();
        $grid = $this->gridManager->getGrid(
            $gridName,
            $pagerParams
        );

        return $grid;
    }

    public function convertResultsObjectToConnection(ResultsObject $result)
    {
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
        $connection
            ->setTotalCount($total)
            ->setPageSize($perPage);

        return $connection;
    }
}
