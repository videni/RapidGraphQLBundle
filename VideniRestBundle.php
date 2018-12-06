<?php

namespace Videni\Bundle\RestBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Videni\Bundle\RestBundle\DependencyInjection\Compiler;
use Oro\Component\ChainProcessor\DependencyInjection\CleanUpProcessorsCompilerPass;
use Oro\Component\ChainProcessor\DependencyInjection\LoadApplicableCheckersCompilerPass;
use Oro\Component\ChainProcessor\SimpleProcessorFactory;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;

class VideniRestBundle extends Bundle
{
     /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new Compiler\ProcessorBagCompilerPass());
        $container->addCompilerPass(new Compiler\FilterFactoryCompilerPass());
        $container->addCompilerPass(new Compiler\QueryExpressionCompilerPass());
        $container->addCompilerPass(new Compiler\RegisterResourcesCompilerPass());

        $container->addCompilerPass(
            new LoadApplicableCheckersCompilerPass('videni_rest.processor_bag', 'videni_rest.processor.applicable_checker')
        );
        $container->addCompilerPass(
            new CleanUpProcessorsCompilerPass(SimpleProcessorFactory::class, 'videni_rest.processor'),
            PassConfig::TYPE_BEFORE_REMOVING
        );
    }
}
