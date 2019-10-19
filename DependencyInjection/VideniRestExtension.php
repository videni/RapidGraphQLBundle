<?php

namespace Videni\Bundle\RestBundle\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Oro\Component\Config\Loader\YamlCumulativeFileLoader;
use Oro\Component\Config\Loader\CumulativeConfigLoader;
use Oro\Component\Config\Loader\ContainerBuilderAdapter;
use Videni\Bundle\RestBundle\Provider\ResourceProvider\ResourceProviderInterface;
use Videni\Bundle\RestBundle\Doctrine\ORM\EntityRepository;
use Videni\Bundle\RestBundle\Doctrine\ORM\ServiceEntityRepository;
use Videni\Bundle\RestBundle\Factory\FactoryInterface;
use Videni\Bundle\RestBundle\Util\DependencyInjectionUtil;
use Videni\Bundle\RestBundle\DependencyInjection\Configuration\ResourceConfiguration;
use Videni\Bundle\RestBundle\Config\Resource\ConfigProvider;
use Videni\Bundle\RestBundle\Normalizer\FormView\FormViewNormalizerInterface;

class VideniRestExtension extends Extension
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
            ->addTag('videni_rest.form_view.normalizer')
            ->setPublic(false);
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

        $configLoader = new CumulativeConfigLoader('videni_rest', $configFileLoaders);
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
            ->addTag('videni_rest.resource_provider')
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
