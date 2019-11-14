<?php

namespace Videni\Bundle\RapidGraphQLBundle;

use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Videni\Bundle\RapidGraphQLBundle\DependencyInjection\Compiler;
use Videni\Bundle\RapidGraphQLBundle\DependencyInjection\VideniRapidGraphQLExtension;

class VideniRapidGraphQLBundle extends Bundle
{
     /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        //make sure RegisterResourcesCompilerPass is registered before FormPass
        $container->addCompilerPass(new Compiler\RegisterResourcesCompilerPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, 100);
        $container->addCompilerPass(new Compiler\RegisterFormViewNormalizerPass());
        $container->addCompilerPass(new Compiler\GraphQLExpressionFunctionPass());
    }

    public function getContainerExtension()
    {
        if (!$this->extension instanceof ExtensionInterface) {
            $this->extension = new VideniRapidGraphQLExtension();
        }

        return $this->extension;
    }
}
