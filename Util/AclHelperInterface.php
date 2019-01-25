<?php

namespace Videni\Bundle\RestBundle\Util;

interface AclHelperInterface
{
    public function apply($query, string $permission = 'VIEW', array $options = []);
}
