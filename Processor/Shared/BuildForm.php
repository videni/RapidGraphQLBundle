<?php

namespace Videni\Bundle\RestBundle\Processor\Shared;

use Doctrine\Common\Util\ClassUtils;
use Videni\Bundle\RestBundle\Form\Extension\ValidationExtension;
use Videni\Bundle\RestBundle\Form\FormHelper;
use Videni\Bundle\RestBundle\Processor\FormContext;
use Oro\Component\ChainProcessor\ContextInterface;
use Oro\Component\ChainProcessor\ProcessorInterface;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Videni\Bundle\RestBundle\Config\Resource\ResourceConfig;
use Symfony\Component\Form\FormFactoryInterface;

class BuildForm implements ProcessorInterface
{
    private $formFactory;

    public function __construct(FormFactoryInterface $formFactory)
    {
        $this->formFactory = $formFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContextInterface $context)
    {
        if ($context->hasForm()) {
            // the form is already built
            return;
        }

        if (!$context->hasResult()) {
            // the entity is not defined
            throw new \RuntimeException(
                'The entity object must be added to the context before creation of the form builder.'
            );
        }

        $this->createForm($context);
    }

    /**
     * @param FormContext $context
     */
    protected function createForm(FormContext $context)
    {
        $resourceConfig = $context->getResourceConfig();

        $formType = $resourceConfig->getOperation($context->getOperationName())->getForm();
        if (null === $formType) {
            return null;
        }

        $options = [
            'validation_groups' => $resourceConfig->getOperationAttribute($context->getOperationName(), 'validationGroups'),
            'data_class' =>  $this->getFormDataClass($context),
        ];

        $form = $this->formFactory->create($formType, $context->getResult(), $options);

        $context->setForm($form);
    }

    /**
     * @param FormContext            $context
     *
     * @return string
     */
    protected function getFormDataClass(FormContext $context)
    {
        $dataClass = $context->getClassName();
        $entity = $context->getResult();
        if (\is_object($entity)) {
            $entityClass = ClassUtils::getClass($entity);
            if ($entityClass !== $dataClass) {
                $dataClass = $entityClass;
            }
        }

        return $dataClass;
    }
}
