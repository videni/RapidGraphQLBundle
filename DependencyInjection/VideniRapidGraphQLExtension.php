<?php

namespace Videni\Bundle\RapidGraphQLBundle\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Oro\Component\Config\Loader\YamlCumulativeFileLoader;
use Oro\Component\Config\Loader\CumulativeConfigLoader;
use Oro\Component\Config\Loader\ContainerBuilderAdapter;
use Videni\Bundle\RapidGraphQLBundle\Provider\ResourceProvider\ResourceProviderInterface;
use Videni\Bundle\RapidGraphQLBundle\Doctrine\ORM\EntityRepository;
use Videni\Bundle\RapidGraphQLBundle\Doctrine\ORM\ServiceEntityRepository;
use Videni\Bundle\RapidGraphQLBundle\Factory\FactoryInterface;
use Videni\Bundle\RapidGraphQLBundle\Util\DependencyInjectionUtil;
use Videni\Bundle\RapidGraphQLBundle\DependencyInjection\Configuration\ResourceConfiguration;
use Videni\Bundle\RapidGraphQLBundle\Config\Resource\ConfigProvider;
use Videni\Bundle\RapidGraphQLBundle\Normalizer\FormView\FormViewNormalizerInterface;

class VideniRapidGraphQLExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $resourceConfigs = $this->loadResourceConfiguration($container);
        $configuration = new Configuration($resourceConfigs['resources']);

        $config = $this->processConfiguration($configuration, $configs);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yaml');

        $this->configureResourceProvider($container);
        $this->configureResource($container, $config['operations'], $resourceConfigs['resources']);

        DependencyInjectionUtil::setConfig($container, $config);

        $container
            ->registerForAutoconfiguration(FormViewNormalizerInterface::class)
            ->addTag('videni_rapid_graphql.form_view.normalizer')
            ->setPublic(false);
    }

    public function getAlias()
    {
        return Configuration::NAME;
    }

    /**
     * @param string $fileName
     *
     * @return array
     */
    private function loadResourceConfiguration($container)
    {
        $configFileLoaders = [
            new YamlCumulativeFileLoader('Resources/config/app/resources.yaml'),
            new YamlCumulativeFileLoader('Resources/config/app/resources.yml')
        ];

        $config = [];

        $configLoader = new CumulativeConfigLoader('videni_rapid_graphql', $configFileLoaders);
        $resources = $configLoader->load(new ContainerBuilderAdapter($container));
        foreach ($resources as $resource) {
            if (array_key_exists(ResourceConfiguration::ROOT_NODE, $resource->data)) {
                $config[] = $resource->data[ResourceConfiguration::ROOT_NODE];
            }
        }

        $configs =  $this->processConfiguration(
            new ResourceConfiguration(),
            $config
        );

        return $configs;
    }

    public function configureResourceProvider($container)
    {
        $container
            ->registerForAutoconfiguration(ResourceProviderInterface::class)
            ->addTag('videni_rapid_graphql.resource_provider')
            ->setPublic(true)
        ;
        $container
            ->registerForAutoconfiguration(EntityRepository::class)
            ->setPublic(true)
        ;
        $container
            ->registerForAutoconfiguration(ServiceEntityRepository::class)
            ->setPublic(true)
        ;
        $container
            ->registerForAutoconfiguration(FactoryInterface::class)
            ->setPublic(true)
        ;
    }

    public function configureResource($container, $operationConfigs, $resourceConfigs)
    {
        $configProviderDef = $container->getDefinition(ConfigProvider::class);
        $configProviderDef->addArgument($resourceConfigs);
        $configProviderDef->addArgument($operationConfigs);
    }
}
