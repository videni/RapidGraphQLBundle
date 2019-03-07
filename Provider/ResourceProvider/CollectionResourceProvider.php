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

class CollectionResourceProvider implements ResourceProviderInterface
{
    private $gridManager;

    private $pagerfantaRepresentationFactory;

    public function __construct(
        PagerfantaFactory $pagerfantaRepresentationFactory,
        Manager $gridManager
    ) {
        $this->pagerfantaRepresentationFactory = $pagerfantaRepresentationFactory;
        $this->gridManager = $gridManager;
    }

    public function get(ResourceContext $context, Request $request)
    {
        if (!in_array($context->getAction(), [ActionTypes::INDEX])) {
            return;
        }

        $gridName = $context->getGrid();
        if (!$gridName) {
            return;
        }

        $grid = $this->gridManager->getGrid($gridName, $request->request->all());

        $route = new Route(
            $request->attributes->get('_route'),
            array_merge($request->attributes->get('_route_params'), $request->query->all())
        );

        /** @var ResultsObject */
        $resultsObject = $grid->getData();

        return $this->pagerfantaRepresentationFactory->createRepresentation($resultsObject->getData(), $route);
    }
}
