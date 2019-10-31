<?php

declare(strict_types=1);

namespace Videni\Bundle\RapidGraphQLBundle\ExpressionLanguage;

use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;

class JsonExpressionLanguageProvider implements ExpressionFunctionProviderInterface
{
    public function getFunctions()
    {
        return [
            ExpressionFunction::fromPhp('json_encode'),
            ExpressionFunction::fromPhp('json_decode'),
        ];
    }
}
