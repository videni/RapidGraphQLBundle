<?php

namespace Videni\Bundle\RestBundle\Serializer\Handler;

use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\JsonSerializationVisitor;
use JMS\Serializer\JsonDeserializationVisitor;
use JMS\Serializer\Context;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\ConstraintViolationList;

class ConstraintViolationListHandler implements SubscribingHandlerInterface
{
    private $serializePayloadFields;

    public function __construct($serializePayloadFields = null)
    {
        $this->serializePayloadFields = $serializePayloadFields;
    }

    public static function getSubscribingMethods()
    {
        return [
            [
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'format' => 'json',
                'type' => ConstraintViolationList::class,
                'method' => 'serializeConstraintValiolationList',
            ],
        ];
    }

    public function serializeConstraintValiolationList(JsonSerializationVisitor $visitor, ConstraintViolationListInterface $violationList, array $type, Context $context)
    {
        $data = $this->getMessagesAndViolations($violationList, $context);

        $visitor->setRoot($data);

        return $data;
    }

    protected function getMessagesAndViolations(ConstraintViolationListInterface $constraintViolationList, Context $context): array
    {
        $violations = $messages = [];

        foreach ($constraintViolationList as $violation) {
            $violationData = [
                'propertyPath' => $violation->getPropertyPath(),
                'message' => $violation->getMessage(),
            ];

            $constraint = $violation->getConstraint();
            if ($this->serializePayloadFields && $constraint && $constraint->payload) {
                // If some fields are whitelisted, only them are added
                $payloadFields = null === $this->serializePayloadFields ? $constraint->payload : array_intersect_key($constraint->payload, array_flip($this->serializePayloadFields));
                $payloadFields && $violationData['payload'] = $payloadFields;
            }

            $violations[] = $violationData;
            $messages[] = ($violationData['propertyPath'] ? "{$violationData['propertyPath']}: " : '').$violationData['message'];
        }

        return [
            'title' => $context->hasAttribute('title') ? $context->getAttribute('title') : 'An error occurred',
            'detail' => $messages ? implode("\n", $messages) : (string) $object,
            'violations' => $violations,
        ];
    }
}
