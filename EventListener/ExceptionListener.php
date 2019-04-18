<?php

declare(strict_types=1);

namespace Videni\Bundle\RestBundle\EventListener;

use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\EventListener\ExceptionListener as BaseExceptionListener;

/**
 * Handles requests errors.
 */
final class ExceptionListener extends BaseExceptionListener
{
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $request = $event->getRequest();
        // Normalize exceptions only for routes managed by VideniRestBundle
        if (
            'html' === $request->getRequestFormat('') ||
            (!$request->attributes->has('_api_resource_class') && !$request->attributes->has('_api_respond'))
        ) {
            return;
        }

        parent::onKernelException($event);
    }
}
