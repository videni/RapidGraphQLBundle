<?php

declare(strict_types=1);

namespace Videni\Bundle\RapidGraphQLBundle\ExpressionLanguage;

use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class NotNullExpressionFunctionProvider implements ExpressionFunctionProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getFunctions(): array
    {
        return [
            new ExpressionFunction('notFoundOnNull', function ($result) {
                return sprintf('(null !== %1$s) ? %1$s : throw new NotFoundHttpException(\'Requested resource is not found.\')', $result);
            }, function ($arguments, $result) {
                if (null === $result) {
                    throw new NotFoundHttpException('Requested resource is not found.');
                }

                return $result;
            }),
        ];
    }
}
