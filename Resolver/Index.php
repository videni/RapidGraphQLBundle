<?php

namespace Videni\Bundle\RapidGraphQLBundle\Resolver;

use Overblog\GraphQLBundle\Definition\Argument;
use Pintushi\Bundle\GridBundle\Grid\Manager;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Videni\Bundle\RapidGraphQLBundle\Provider\ResourceProvider\ChainResourceProvider;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;

class Index implements ResolverInterface
{
    private $gridManager;
    private $authorizationChecker;
    private $resourceContextResolver;

    public function __construct(
        ResourceContextResolver $resourceContextResolver,
        Manager $gridManager,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        $this->resourceContextResolver = $resourceContextResolver;
        $this->gridManager = $gridManager;
        $this->authorizationChecker = $authorizationChecker;
    }

    public function __invoke(Argument $args, $operationName, $actionName, Request $request)
    {
        $context = $this->resourceContextResolver->resolveResourceContext($operationName, $actionName);
        $grid = $this->gridManager->getGrid(
            $context->getGrid(),
            $args->getArrayCopy()
        );

        /**
         * @var ResultsObject
         */
        $data = $grid->getData();
        $aclResource  = $grid->getConfig()->getAclResource();

        if($aclResource && $this->authorizationChecker->isGranted($aclResource)) {
            throw new AccessDeniedHttpException('You are not allowed to access this resource.');
        }

        return $this->connectionBuilder->connectionFromArraySlice($data, $args, [
            'sliceStart' => $data->getCursor(),
            'arrayLength' => $data->getTotalRecords(),
        ]);
    }
}
