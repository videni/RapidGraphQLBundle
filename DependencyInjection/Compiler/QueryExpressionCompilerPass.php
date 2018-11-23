<?php

namespace App\Bundle\RestBundle\DependencyInjection\Compiler;

use App\Bundle\RestBundle\Util\DependencyInjectionUtil;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use App\Bundle\RestBundle\Collection\QueryExpressionVisitorFactory;

/**
 * Registers all possible ORM expressions that can be used in Criteria object.
 * @see \App\Bundle\RestBundle\Util\CriteriaConnector::applyCriteria
 */
class QueryExpressionCompilerPass implements CompilerPassInterface
{
    private const COMPOSITE_EXPRESSION_TAG       = 'app_rest.query.composite_expression';
    private const COMPOSITE_EXPRESSION_TYPE      = 'type';
    private const COMPARISON_EXPRESSION_TAG      = 'app_rest.query.comparison_expression';
    private const COMPARISON_EXPRESSION_OPERATOR = 'operator';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $compositeExpressions = $this->getExpressions(
            $container,
            self::COMPOSITE_EXPRESSION_TAG,
            self::COMPOSITE_EXPRESSION_TYPE
        );
        $comparisonExpressions = $this->getExpressions(
            $container,
            self::COMPARISON_EXPRESSION_TAG,
            self::COMPARISON_EXPRESSION_OPERATOR
        );
        $container->getDefinition(QueryExpressionVisitorFactory::class)
            ->replaceArgument(0, $compositeExpressions)
            ->replaceArgument(1, $comparisonExpressions);
    }

    /**
     * @param ContainerBuilder $container
     * @param string           $tagName
     * @param string           $operatorPlaceholder
     *
     * @return array [operator name => provider definition, ...]
     */
    private function getExpressions(ContainerBuilder $container, string $tagName, string $operatorPlaceholder): array
    {
        $services = [];
        $taggedServices = $container->findTaggedServiceIds($tagName);
        foreach ($taggedServices as $id => $tags) {
            foreach ($tags as $tag) {
                $services[DependencyInjectionUtil::getPriority($tag)][] = [
                    DependencyInjectionUtil::getRequiredAttribute($tag, $operatorPlaceholder, $id, $tagName),
                    new Reference($id)
                ];
            }
        }
        if (empty($services)) {
            return [];
        }

        $expressions = [];
        $services = DependencyInjectionUtil::sortByPriorityAndFlatten($services);
        foreach ($services as list($expressionType, $serviceRef)) {
            $expressions[$expressionType] = $serviceRef;
        }

        return $expressions;
    }
}
