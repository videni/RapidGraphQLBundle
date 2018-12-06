<?php

namespace Videni\Bundle\RestBundle\DependencyInjection\Compiler;

use Videni\Bundle\RestBundle\Form\ResolvedFormTypeFactory;
use Videni\Bundle\RestBundle\Form\SwitchableFormRegistry;
use Videni\Bundle\RestBundle\Util\DependencyInjectionUtil;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\LogicException;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Form\FormRegistry;
use Videni\Bundle\RestBundle\Form\FormExtension;
use Videni\Bundle\RestBundle\Form\FormExtensionState;
use Videni\Bundle\RestBundle\Form\Extension\SwitchableDependencyInjectionExtension;
use Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass;
use Videni\Bundle\RestBundle\Form\FormHelper;
use Symfony\Component\DependencyInjection\Argument\IteratorArgument;

/**
 * Configures all services required for Data API forms.
 */
class FormCompilerPass implements CompilerPassInterface
{
    private const FORM_REGISTRY_SERVICE_ID                 = 'form.registry';
    private const FORM_EXTENSION_SERVICE_ID                = 'form.extension';
    private const FORM_TYPE_TAG                            = 'form.type';
    private const FORM_TYPE_EXTENSION_TAG                  = 'form.type_extension';
    private const FORM_TYPE_GUESSER_TAG                    = 'form.type_guesser';
    private const FORM_TYPE_FACTORY_SERVICE_ID             = 'form.resolved_type_factory';

    private const VIDENI_FORM_TYPE_FACTORY_SERVICE_ID         = 'videni_rest.form.resolved_type_factory';
    private const VIDENI_FORM_TYPE_TAG                        = 'videni_rest.form.type';
    private const VIDENI_FORM_TYPE_EXTENSION_TAG              = 'videni_rest.form.type_extension';
    private const VIDENI_FORM_TYPE_GUESSER_TAG                = 'videni_rest.form.type_guesser';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(self::FORM_REGISTRY_SERVICE_ID) ||
            !$container->hasDefinition(SwitchableDependencyInjectionExtension::class)
        ) {
            return;
        }

        $config = DependencyInjectionUtil::getConfig($container);

        //replace symfony form registry with ours
        $formRegistryDef = $container->getDefinition(self::FORM_REGISTRY_SERVICE_ID);
        $this->assertExistingFormRegistry($formRegistryDef, $container);
        $formRegistryDef->setClass(SwitchableFormRegistry::class);
        $formRegistryDef->replaceArgument(0, [new Reference(SwitchableDependencyInjectionExtension::class)]);
        $formRegistryDef->addArgument(new Reference(FormExtensionState::class));

        // decorates the "form.resolved_type_factory" service
        $this->decorateFormTypeFactory($container);

        $this->buildSwitchableDependencyInjectionExtensionService($container, $config);

        $dataTypeMappings = [];
        foreach ($config['form_type_alias'] as $dataType => $value) {
            $dataTypeMappings[$dataType] = [$value['form_type'], $value['options']];
        }
        $container
            ->getDefinition(FormHelper::class)
            ->addArgument($dataTypeMappings)
        ;
    }

    private function buildSwitchableDependencyInjectionExtensionService($container, $config)
    {
        $apiFormDef = $container->getDefinition(SwitchableDependencyInjectionExtension::class);
        if ($container->hasDefinition(self::FORM_EXTENSION_SERVICE_ID)) {
            $container->getDefinition(self::FORM_EXTENSION_SERVICE_ID)->setPublic(true);

            $apiFormDef->addMethodCall(
                'addExtension',
                [SwitchableFormRegistry::DEFAULT_EXTENSION, self::FORM_EXTENSION_SERVICE_ID]
            );
        }
        if ($container->hasDefinition(FormExtension::class)) {
            $apiFormExtensionDef = $container->getDefinition(FormExtension::class);
            $apiFormExtensionDef->setPublic(true);

            $apiFormDef->addMethodCall(
                'addExtension',
                [SwitchableFormRegistry::API_EXTENSION, FormExtension::class]
            );

            // reuse existing form types, form type extensions and form type guessers
            $formTypeClassNames = [];
            $formTypeServiceIds = [];
            foreach ($config['form_types'] as $formType) {
                if ($container->hasDefinition($formType)) {
                    $formTypeServiceIds[] = $formType;
                } else {
                    $formTypeClassNames[] = $formType;
                }
            }
            $this->addFormTag(
                $container,
                $formTypeServiceIds,
                self::FORM_TYPE_TAG,
                self::VIDENI_FORM_TYPE_TAG
            );

            $this->addFormTag(
                $container,
                $config['form_type_extensions'],
                self::FORM_TYPE_EXTENSION_TAG,
                self::VIDENI_FORM_TYPE_EXTENSION_TAG
            );
            $this->addFormTag(
                $container,
                $config['form_type_guessers'],
                self::FORM_TYPE_GUESSER_TAG,
                self::VIDENI_FORM_TYPE_GUESSER_TAG
            );

            // load form types, form type extensions and form type guessers for Data API form extension
            list($formTypeContainer, $formTypes) = $this->getFormTypes($container, $formTypeClassNames);
            $apiFormExtensionDef->replaceArgument(0, $formTypeContainer);
            $apiFormExtensionDef->replaceArgument(1, $formTypes);
            $apiFormExtensionDef->replaceArgument(2, $this->getFormTypeExtensions($container));
            $apiFormExtensionDef->replaceArgument(3, $this->getFormTypeGuessers($container));
        }
    }
    /**
     * @param ContainerBuilder $container
     */
    private function decorateFormTypeFactory(ContainerBuilder $container)
    {
        $container
            ->register(self::VIDENI_FORM_TYPE_FACTORY_SERVICE_ID, ResolvedFormTypeFactory::class)
            ->setArguments([
                new Reference(self::VIDENI_FORM_TYPE_FACTORY_SERVICE_ID . '.inner'),
                new Reference(FormExtensionState::class)
            ])
            ->setPublic(false)
            ->setDecoratedService(self::FORM_TYPE_FACTORY_SERVICE_ID);
    }

    /**
     * @param Definition       $formRegistryDef
     * @param ContainerBuilder $container
     */
    private function assertExistingFormRegistry(Definition $formRegistryDef, ContainerBuilder $container)
    {
        $formRegistryClass = $formRegistryDef->getClass();
        if (0 === strpos($formRegistryClass, '%')) {
            $formRegistryClass = $container->getParameter(substr($formRegistryClass, 1, -1));
        }
        if (FormRegistry::class !== $formRegistryClass) {
            throw new LogicException(sprintf(
                'Expected class of the "%s" service is "%s", actual class is "%s".',
                self::FORM_REGISTRY_SERVICE_ID,
                FormRegistry::class,
                $formRegistryClass
            ));
        }

        $formExtensions = $formRegistryDef->getArgument(0);
        if (!is_array($formExtensions)) {
            throw new LogicException(sprintf(
                'Cannot register Data API form extension because it is expected'
                . ' that the first argument of "%s" service is array. "%s" given.',
                self::FORM_REGISTRY_SERVICE_ID,
                is_object($formExtensions) ? get_class($formExtensions) : gettype($formExtensions)
            ));
        } elseif (count($formExtensions) !== 1) {
            throw new LogicException(sprintf(
                'Cannot register Data API form extension because it is expected'
                . ' that the first argument of "%s" service is array contains only one element.'
                . ' Detected the following form extension: %s.',
                self::FORM_REGISTRY_SERVICE_ID,
                implode(
                    ', ',
                    array_map(
                        function (Reference $ref) {
                            return (string)$ref;
                        },
                        $formExtensions
                    )
                )
            ));
        }
    }

    /**
     * @param ContainerBuilder $container
     * @param string[]         $serviceIds
     * @param string           $tagName
     * @param string           $apiTagName
     */
    private function addFormTag(ContainerBuilder $container, array $serviceIds, $tagName, $apiTagName)
    {
        foreach ($serviceIds as $serviceId) {
            if ($container->hasDefinition($serviceId)) {
                $definition = $container->getDefinition($serviceId);
                $tags = $definition->getTag($tagName);
                foreach ($tags as $tag) {
                    $definition->addTag($apiTagName, $tag);
                }
            }
        }
    }

    /**
     * @param ContainerBuilder $container
     * @param string[]         $formTypeClassNames
     *
     * @return array
     */
    private function getFormTypes(ContainerBuilder $container, array $formTypeClassNames)
    {
        // Get service locator argument
        $servicesMap = array();

        $types = array_fill_keys($formTypeClassNames, null);
        foreach ($container->findTaggedServiceIds(self::VIDENI_FORM_TYPE_TAG) as $serviceId => $tag) {
            $alias = DependencyInjectionUtil::getAttribute($tag[0], 'alias', $serviceId);
            $types[$alias] = $serviceId;

            $serviceDefinition = $container->getDefinition($serviceId);
            $servicesMap[$formType = $serviceDefinition->getClass()] = new Reference($serviceId);
        }

        return [ServiceLocatorTagPass::register($container, $servicesMap), $types];
    }

    /**
     * @param ContainerBuilder $container
     *
     * @return array
     */
    private function getFormTypeExtensions(ContainerBuilder $container)
    {
        $typeExtensions = [];
        foreach ($container->findTaggedServiceIds(self::VIDENI_FORM_TYPE_EXTENSION_TAG) as $serviceId => $tag) {
            $alias = DependencyInjectionUtil::getAttribute($tag[0], $this->getTagKeyForExtension(), $serviceId);
            $typeExtensions[$alias][] = new Reference($serviceId);
        }

        foreach ($typeExtensions as $extendedType => $extensions) {
            $typeExtensions[$extendedType] = new IteratorArgument($extensions);
        }
        return $typeExtensions;
    }

    /**
     * @param ContainerBuilder $container
     *
     * @return array
     */
    private function getFormTypeGuessers(ContainerBuilder $container)
    {
        $guessers = [];
        foreach ($container->findTaggedServiceIds(self::VIDENI_FORM_TYPE_GUESSER_TAG) as $serviceId => $tags) {
            foreach ($tags as $tag) {
                $guessers[$serviceId] = DependencyInjectionUtil::getPriority($tag);
            }
        }
        arsort($guessers, SORT_NUMERIC);

        return new IteratorArgument(array_map(function ($serviceId) {
            return new Reference($serviceId);
        }, array_keys($guessers)));
    }

    /**
     * Provide compatibility between Symfony 2.8 and version below this
     * @return string
     */
    public function getTagKeyForExtension()
    {
        return method_exists('Symfony\Component\Form\AbstractType', 'getBlockPrefix')
            ? 'extended_type'
            : 'alias';
    }
}
