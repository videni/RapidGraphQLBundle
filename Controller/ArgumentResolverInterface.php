<?php

namespace Videni\Bundle\RapidGraphQLBundle\Controller;

use Videni\Bundle\RapidGraphQLBundle\Definition\Argument;

/**
 * An ArgumentResolverInterface instance knows how to determine the
 * arguments for a specific action.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
interface ArgumentResolverInterface
{
    /**
     * Returns the arguments to pass to the controller.
     *
     * @param callable $controller
     *
     * @return array An array of arguments to pass to the controller
     *
     * @throws \RuntimeException When no value could be provided for a required argument
     */
    public function getArguments(Argument $request, $controller);
}
