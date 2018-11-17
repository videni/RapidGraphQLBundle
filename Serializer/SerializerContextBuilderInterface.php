<?php

declare(strict_types=1);

namespace App\Bundle\RestBundle\Serializer;

use App\Bundle\RestBundle\Exception\RuntimeException;
use Symfony\Component\HttpFoundation\Request;
use JMS\Serializer\Context;

/**
 * Builds the context used by the Symfony Serializer.
 */
interface SerializerContextBuilderInterface
{
    /**
     * Creates a serialization context from a Request.
     *
     *
     * @throws RuntimeException
     */
    public function createFromRequest(Request $request, bool $normalization, array $extractedAttributes): Context;
}
