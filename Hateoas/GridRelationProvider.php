<?php

namespace Videni\Bundle\RestBundle\Hateoas;

use Hateoas\Configuration\Relation;
use Hateoas\Configuration\Route;
use Videni\Bundle\RestBundle\Context\ResourceContextStorage;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Videni\Bundle\RestBundle\Security\ResourceAccessChecker;
use JMS\Serializer\Expression\ExpressionEvaluatorInterface;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\PropertyAccess\PropertyAccess;

class GridRelationProvider
{
    const ACTION_GROUP = 'main';

    private $resourceContextStorage;
    private $resourceAccessChecker;
    private $expressionEvaluator;

    public function __construct(
        ResourceContextStorage $resourceContextStorage,
        ResourceAccessChecker $resourceAccessChecker,
        ExpressionEvaluatorInterface $expressionEvaluator
    )
    {
        $this->resourceContextStorage = $resourceContextStorage;
        $this->resourceAccessChecker = $resourceAccessChecker;
        $this->expressionEvaluator = $expressionEvaluator;
    }

    public function getRelations()
    {
        $context = $this->resourceContextStorage->getContext();

        $grid = $context->getGrid();

        $propertyAccesser = PropertyAccess::createPropertyAccessor();

        if (!$grid->hasActionGroup(self::ACTION_GROUP)) {
            return;
        }

        $group = $grid->getActionGroup(self::ACTION_GROUP);

        $relations = [];

        $variables = [];

        foreach($group->getActions() as $name => $action) {
            $accessControl = $action->getAccessControl();
            $isGranted = true;
            if ($accessControl) {
                $isGranted = $this->resourceAccessChecker->isGranted($accessControl);
            }

            if (!$isGranted) {
                continue;
            }

            $route = $propertyAccesser->getValue($action->getOptions(), '[link][route]');
            $parameters = $propertyAccesser->getValue($action->getOptions(), '[link][parameters]');

            $relations[] = new Relation(
                $name,
                new Route(
                    $route,
                    $this->evaluateParameters($parameters)
                )
            );
        }

        return $relations;
    }

    protected function evaluateParameters($parameters )
    {
        $parameters = is_array($parameters)? $parameters: [];
        $newParamters = [];
        foreach($parameters as $key => $value) {
            $newParamters[$key] = $this->expressionEvaluator->evaluate($value, $variables);
        }

        return $newParamters;
    }
}
