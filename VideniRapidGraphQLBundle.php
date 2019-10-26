<?php

namespace Videni\Bundle\RapidGraphQLBundle;

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

        $container->addCompilerPass(new Compiler\RegisterResourcesCompilerPass());
        $container->addCompilerPass(new Compiler\RegisterFormViewNormalizerPass());
    }

    public function getContainerExtension()
    {
        if (!$this->extension instanceof ExtensionInterface) {
            $this->extension = new VideniRapidGraphQLExtension();
        }

        return $this->extension;
    }
}
