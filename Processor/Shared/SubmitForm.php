<?php

namespace Videni\Bundle\RestBundle\Processor\Shared;

use VideniBundleRestBundle\Processor\FormContext;
use Oro\Component\ChainProcessor\ContextInterface;
use Oro\Component\ChainProcessor\ProcessorInterface;

/**
 * Transforms the request data via the form from the context.
 */
class SubmitForm implements ProcessorInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContextInterface $context)
    {
        /** @var FormContext $context */

        if (!$context->hasForm()) {
            // no form
            return;
        }

        $form = $context->getForm();
        if ($form->isSubmitted()) {
            // the form is already submitted
            return;
        }

        /**
         * always use $clearMissing = false, more details in:
         * @see \VideniBundleRestBundle\Form\FormValidationHandler::validate
         * @see \VideniBundleRestBundle\Processor\Shared\BuildFormBuilder::$enableFullValidation
         */
        $form->submit($this->prepareRequestData($context->getRequest()->request->all()), false);
    }

    /**
     * @param array $requestData
     *
     * @return array
     */
    protected function prepareRequestData(array $requestData)
    {
        /**
         * as Symfony Form treats false as NULL due to checkboxes
         * @see \Symfony\Component\Form\Form::submit
         * we have to convert false to its string representation here
         */
        \array_walk_recursive(
            $requestData,
            function (&$value) {
                if (false === $value) {
                    $value = 'false';
                }
            }
        );

        return $requestData;
    }
}
