<?php

namespace Videni\Bundle\RestBundle\Processor\Update;

use Videni\Bundle\RestBundle\Processor\SingleItemContext;
use Videni\Bundle\RestBundle\Processor\FormContext;
use Videni\Bundle\RestBundle\Processor\FormContextTrait;

class UpdateContext extends SingleItemContext implements FormContext
{
    use FormContextTrait;
}
