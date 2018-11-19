<?php

namespace App\Bundle\RestBundle\Processor;

use App\Bundle\RestBundle\Processor\Context;
use Oro\Component\ChainProcessor\ContextInterface;
use Oro\Component\ChainProcessor\ProcessorInterface;
use App\Bundle\RestBundle\Metadata\Resource\ResourceMetadata;
use App\Bundle\RestBundle\Validator\ValidatorInterface;

final class ValidationProcessor implements ProcessorInterface
{
    private $validator;

    /**
     * @throws InvalidArgumentException
     */
    public function __construct(
        ValidatorInterface $validator
    ) {
        $this->validator = $validator;
    }

     /**
     * {@inheritdoc}
     */
    public function process(ContextInterface $context)
    {
        $validationGroups = $context->getMetadata()->getOperationAttribute($context->getClassName(), 'validation_groups', null, true);

        $data = $context->get('denormalized_data');

        $this->validator->validate($data, ['groups' => $validationGroups]);
    }
}
