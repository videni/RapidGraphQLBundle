<?php

namespace Videni\Bundle\RapidGraphQLBundle\GraphQL\Resolver;

use Videni\Bundle\RapidGraphQLBundle\Definition\Argument;
use Pintushi\Bundle\GridBundle\Grid\Manager;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;
use Overblog\GraphQLBundle\Relay\Connection\ConnectionBuilder;
use Videni\Bundle\RapidGraphQLBundle\Controller\ControllerResolver;
use Videni\Bundle\RapidGraphQLBundle\Security\ResourceAccessCheckerInterface;


/**
 * @todo: Don't calculate total count if total is not requested.
 */
class Index extends AbstractResolver implements ResolverInterface
{
    use ConnectionTrait {
        ConnectionTrait::__construct as private connectionTraitConstruct;
    }

    public function __construct(
        ResourceContextResolver $resourceContextResolver,
        ControllerResolver $controllerResolver,
        ControllerExecutor $controllerExecutor,
        ResourceAccessCheckerInterface $resourceAccessChecker,
        Manager $gridManager,
        ConnectionBuilder $connectionBuilder = null
    ) {
        parent::__construct($resourceContextResolver, $controllerResolver, $controllerExecutor, $resourceAccessChecker);
        $this->connectionTraitConstruct($gridManager, $connectionBuilder ?? new ConnectionBuilder());
    }

    public function __invoke(Argument $args, $operationName, $actionName)
    {
        $context = $this->resourceContextResolver->resolveResourceContext($operationName, $actionName);

        $this->checkPermission(null, $context->getAction(), $args);
        // $args->attributes->set('data', $result);
        if ($controller = $this->controllerResolver->getController($context)) {
            return $this->controllerExecutor->execute($controller, $args);
        }

        return $this->getConnection($args, $context->getGrid());
    }
}
