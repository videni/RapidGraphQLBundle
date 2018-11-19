<?php

namespace App\Bundle\RestBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use App\Bundle\RestBundle\DependencyInjection\Compiler;
use Oro\Component\ChainProcessor\DependencyInjection\CleanUpProcessorsCompilerPass;
use Oro\Component\ChainProcessor\DependencyInjection\LoadApplicableCheckersCompilerPass;
use Oro\Component\ChainProcessor\SimpleProcessorFactory;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;

class AppRestBundle extends Bundle
{
     /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new Compiler\ProcessorBagCompilerPass());

        $container->addCompilerPass(
            new LoadApplicableCheckersCompilerPass('app_rest.processor_bag', 'app_rest.processor.applicable_checker')
        );
        $container->addCompilerPass(
            new CleanUpProcessorsCompilerPass(SimpleProcessorFactory::class, 'app_rest.processor'),
            PassConfig::TYPE_BEFORE_REMOVING
        );
    }
}
