<?php

namespace Videni\Bundle\RestBundle\Form;

use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\Form\ResolvedFormTypeFactoryInterface;
use Symfony\Component\Form\ResolvedFormTypeInterface;

class ResolvedFormTypeFactory implements ResolvedFormTypeFactoryInterface
{
    /** @var ResolvedFormTypeFactoryInterface */
    protected $defaultFactory;

    /** @var FormExtensionCheckerInterface */
    protected $formExtensionChecker;

    /**
     * @param ResolvedFormTypeFactoryInterface $defaultFactory
     * @param FormExtensionCheckerInterface    $formExtensionChecker
     */
    public function __construct(
        ResolvedFormTypeFactoryInterface $defaultFactory,
        FormExtensionCheckerInterface $formExtensionChecker
    ) {
        $this->defaultFactory = $defaultFactory;
        $this->formExtensionChecker = $formExtensionChecker;
    }

    /**
     * {@inheritdoc}
     */
    public function createResolvedType(
        FormTypeInterface $type,
        array $typeExtensions,
        ResolvedFormTypeInterface $parent = null
    ) {
        $resolvedType = $this->defaultFactory->createResolvedType($type, $typeExtensions, $parent);
        if ($this->formExtensionChecker->isApiFormExtensionActivated()) {
            $resolvedType = new ApiResolvedFormType($resolvedType);
        }

        return $resolvedType;
    }
}
