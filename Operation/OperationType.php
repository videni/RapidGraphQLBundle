<?php

declare(strict_types=1);

namespace App\Bundle\RestBundle\Operation;

final class OperationType
{
    const ITEM = 'item';
    const COLLECTION = 'collection';
    const TYPES = [self::ITEM, self::COLLECTION];
}
