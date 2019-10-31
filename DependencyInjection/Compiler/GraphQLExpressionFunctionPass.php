<?php

declare(strict_types=1);

namespace Videni\Bundle\RapidGraphQLBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Videni\Bundle\RapidGraphQLBundle\ExpressionLanguage\JsonExpressionLanguageProvider;

final class GraphQLExpressionFunctionPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container): void
    {
        $definition = $container->findDefinition('overblog_graphql.expression_language');

        $definition->addMethodCall('registerProvider', [new Reference(JsonExpressionLanguageProvider::class)]);
    }
}
