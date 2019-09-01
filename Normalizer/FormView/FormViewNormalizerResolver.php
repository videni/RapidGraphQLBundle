<?php

namespace Videni\Bundle\RestBundle\Normalizer\FormView;

use Zend\Stdlib\PriorityQueue;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

final class FormViewNormalizerResolver
{
    /**
     * @var PriorityQueue|FormViewNormalizerInterface[]
     */
    private $normalizers;

    public function __construct()
    {
        $this->normalizers = new PriorityQueue();
    }

    public function addNormalizer(FormViewNormalizerInterface $normalizer, int $priority = 0): void
    {
        $this->normalizers->insert($normalizer, $priority);
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(FormInterface $form, FormView $formView, array $ancestries): ?FormViewNormalizerInterface
    {
        foreach ($this->normalizers as $normalizer) {
            if($normalizer->support($form, $formView, $ancestries)) {
                return $normalizer;
            }
        }

        return null;
    }
}
