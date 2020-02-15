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

    private $pageSize;

    public function __construct($edges = [], PageInfoInterface $pageInfo = null)
    {
        $this->edges = $edges;
        $this->pageInfo = $pageInfo;
    }

    public function getEdges()
    {
        return $this->edges;
    }


    public function setEdges(iterable $edges): self
    {
        $this->edges = $edges;

        return $this;
    }

    public function getPageInfo(): ? PageInfoInterface
    {
        return $this->pageInfo;
    }


    public function setPageInfo(PageInfoInterface $pageInfo): self
    {
        $this->pageInfo = $pageInfo;

        return $this;
    }

    public function getTotalCount()
    {
        return \is_callable($this->totalCount) ? \call_user_func($this->totalCount) : $this->totalCount;
    }

    public function setTotalCount($totalCount): self
    {
        $this->totalCount = $totalCount;

        return $this;
    }

    public function setPageSize($pageSize): self
    {
        $this->pageSize = $pageSize;

        return $this;
    }

    public function getPageSize()
    {
        return $this->pageSize;
    }

    public function getPageCount()
    {
        $totalCount = $this->getTotalCount();

        if (!$this->pageSize) {
            return null;
        }

        return ceil($totalCount/$this->pageSize);
    }
}
