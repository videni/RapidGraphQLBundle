<?php

namespace Videni\Bundle\RapidGraphQLBundle\GraphQL\Resolver;

use Overblog\GraphQLBundle\Definition\Argument;
use Pintushi\Bundle\GridBundle\Grid\Manager;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;
use Symfony\Component\HttpFoundation\Request;
use  Overblog\GraphQLBundle\Relay\Connection\ConnectionBuilder;
use Videni\Bundle\RapidGraphQLBundle\Controller\ControllerResolver;
use Videni\Bundle\RapidGraphQLBundle\Security\ResourceAccessCheckerInterface;

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

    public function __invoke(Argument $args, $operationName, $actionName, Request $request)
    {
        $request->attributes->set('arguments', $args);

        $pagerParams = isset($args['input'])?  $args['input'] : [];

        $context = $this->resourceContextResolver->resolveResourceContext($operationName, $actionName);

        $this->checkPermission(null, $context->getAction(), $request);

        $grid = $this->gridManager->getGrid(
            $context->getGrid(),
            $pagerParams
        );

        /**
         * @var ResultsObject
         */
        $result = $grid->getData();
        $request->attributes->set('data', $result);

        if ($controller = $this->controllerResolver->getController($context)) {
            return $this->controllerExecutor->execute($controller, $request);
        }

        $arrayLength =  isset($pagerParams['last']) ?  $result->getTotalRecords() : ($result->getCursor() + count($result->getData()));

        $connection = $this
            ->connectionBuilder
            ->connectionFromArraySlice(
                $result->getData(),
                $pagerParams, [
                    'sliceStart' =>  $result->getCursor(),
                    'arrayLength' => $arrayLength
                ]
            );

        $connection->setTotalCount($result->getTotalRecords());


        return $connection;
    }
}
