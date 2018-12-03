<?php

namespace Videni\Bundle\RestBundle\Form;

use Videni\Bundle\RestBundle\Form\DataTransformer\NullTransformer;
use Videni\Bundle\RestBundle\Form\DataTransformer\NullValueTransformer;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\FormBuilder as BaseFormBuilder;

class FormBuilder extends BaseFormBuilder
{
    /** @var array|null */
    private $emptyViewTransformers;

    /**
     * {@inheritdoc}
     */
    public function addViewTransformer(DataTransformerInterface $viewTransformer, $forcePrepend = false)
    {
        $this->emptyViewTransformers = null;

        if (!$viewTransformer instanceof NullValueTransformer) {
            $viewTransformer = new NullValueTransformer($viewTransformer);
        }

        return parent::addViewTransformer($viewTransformer, $forcePrepend);
    }

    /**
     * {@inheritdoc}
     */
    public function resetViewTransformers()
    {
        $this->emptyViewTransformers = null;

        return parent::resetViewTransformers();
    }

    /**
     * {@inheritdoc}
     */
    public function getViewTransformers()
    {
        $viewTransformers = parent::getViewTransformers();
        // if a field does not have any view transformer use a transformer that does nothing,
        // this is required because by default Symfony Forms replaces NULL with empty string
        if (empty($viewTransformers)) {
            if (null === $this->emptyViewTransformers) {
                $this->emptyViewTransformers = [NullTransformer::getInstance()];
            }
            $viewTransformers = $this->emptyViewTransformers;
        }

        return $viewTransformers;
    }
}
