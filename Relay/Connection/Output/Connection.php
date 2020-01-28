<?php

declare(strict_types=1);

namespace Videni\Bundle\RapidGraphQLBundle\Relay\Connection\Output;

use Overblog\GraphQLBundle\Relay\Connection\ConnectionInterface;
use Overblog\GraphQLBundle\Relay\Connection\EdgeInterface;
use Overblog\GraphQLBundle\Relay\Connection\PageInfoInterface;
use Overblog\GraphQLBundle\Relay\Connection\Output\DeprecatedPropertyPublicAccessTrait;

/**
 * This one is different from the one from `OverblogGraphQLBundle` in which
 * the toal is calculated ahead, but we want to defer its calculation.
 */
class Connection
{
    use DeprecatedPropertyPublicAccessTrait;

    /** @var EdgeInterface[] */
    protected $edges;

    /** @var PageInfoInterface */
    protected $pageInfo;

    /** @var int|callable */
    protected $totalCount;

    public function __construct($edges = [], PageInfoInterface $pageInfo = null)
    {
        $this->edges = $edges;
        $this->pageInfo = $pageInfo;
    }

    public function getEdges()
    {
        return $this->edges;
    }


    public function setEdges(iterable $edges): void
    {
        $this->edges = $edges;
    }

    public function getPageInfo(): ? PageInfoInterface
    {
        return $this->pageInfo;
    }


    public function setPageInfo(PageInfoInterface $pageInfo): void
    {
        $this->pageInfo = $pageInfo;
    }

    public function getTotalCount()
    {
        return \is_callable($this->totalCount) ? \call_user_func($this->totalCount) : $this->totalCount;
    }

    public function setTotalCount($totalCount): void
    {
        $this->totalCount = $totalCount;
    }
}
