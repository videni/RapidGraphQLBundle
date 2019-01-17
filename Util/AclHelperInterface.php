<?php

namespace Videni\Bundle\RestBundle\Util;

interface AclHelperInterface
{
    public function apply($query, $permission = 'VIEW', $checkRelations = true);
}
