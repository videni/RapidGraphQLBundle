<?php
declare(strict_types=1);

namespace Videni\Bundle\RapidGraphQLBundle\Exception;

class DeleteHandlingException extends \Exception
{
    /** @var int */
    protected $apiResponseCode;

    public function __construct(
        string $message = 'Ups, something went wrong during deleting a resource, please try again.',
        int $apiResponseCode = 500,
        int $code = 0,
        ?\Exception $previous = null
    ) {
        parent::__construct($message, $code, $previous);

        $this->apiResponseCode = $apiResponseCode;
    }

    public function getFlash(): string
    {
        return $this->flash;
    }

    public function getApiResponseCode(): int
    {
        return $this->apiResponseCode;
    }
}
