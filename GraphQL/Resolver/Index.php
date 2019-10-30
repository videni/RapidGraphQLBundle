<?php

namespace Videni\Bundle\RapidGraphQLBundle\GraphQL\Resolver;

use Overblog\GraphQLBundle\Definition\Argument;
use Pintushi\Bundle\GridBundle\Grid\Manager;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;
use Symfony\Component\HttpFoundation\Request;
use  Overblog\GraphQLBundle\Relay\Connection\ConnectionBuilder;

class Index implements ResolverInterface
{
    private $gridManager;
    private $authorizationChecker;
    private $resourceContextResolver;
    private $connectionBuilder;

    public function __construct(
        ResourceContextResolver $resourceContextResolver,
        Manager $gridManager,
        AuthorizationCheckerInterface $authorizationChecker,
        ControllerExecutor $controllerExecutor,
        ConnectionBuilder $connectionBuilder = null
    ) {
        $this->resourceContextResolver = $resourceContextResolver;
        $this->gridManager = $gridManager;
        $this->authorizationChecker = $authorizationChecker;
        $this->connectionBuilder = $connectionBuilder ?? new ConnectionBuilder();
        $this->controllerExecutor = $controllerExecutor;
    }

    public function __invoke(Argument $args, $operationName, $actionName, Request $request)
    {
        $pagerParams = isset($args['input'])?  $args['input'] : [];

        $context = $this->resourceContextResolver->resolveResourceContext($operationName, $actionName);
        $grid = $this->gridManager->getGrid(
            $context->getGrid(),
            $pagerParams
        );

        /**
         * @var ResultsObject
         */
        $result = $grid->getData();
        $request->attributes->set('data', $result);

        $result = $this->controllerExecutor->execute($context, $request);

        $aclResource  = $grid->getConfig()->getAclResource();
        if($aclResource && !$this->authorizationChecker->isGranted($aclResource)) {
            throw new AccessDeniedHttpException('You are not allowed to access this resource.');
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
