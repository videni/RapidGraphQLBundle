<?php

namespace App\Bundle\RestBundle\Processor;

/**
 * The main processor for "create" action.
 */
class CreateProcessor extends RequestActionProcessor
{
    /**
     * {@inheritdoc}
     */
    protected function createContextObject()
    {
        return new CreateContext();
    }

    /**
     * {@inheritdoc}
     */
    protected function getLogContext(NormalizeResultContext $context): array
    {
        $result = parent::getLogContext($context);
        if (array_key_exists('id', $result) && empty($result['id'])) {
            unset($result['id']);
        }

        return $result;
    }
}
