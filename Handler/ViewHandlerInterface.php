<?php

declare(strict_types=1);

namespace Videni\Bundle\RestBundle\Handler;

use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Response;
use Videni\Bundle\RestBundle\Processor\Context;

interface ViewHandlerInterface
{
    /**
     * @param Context $context
     * @param View $view
     *
     * @return Response
     */
    public function handle(Context $context, View $view): Response;
}
