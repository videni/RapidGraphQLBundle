<?php

namespace Videni\Bundle\RapidGraphQLBundle\GraphQL\Resolver;

use Videni\Bundle\RapidGraphQLBundle\Definition\Argument;
use Pintushi\Bundle\GridBundle\Grid\Manager;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;
use Overblog\GraphQLBundle\Relay\Connection\ConnectionBuilder;
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
