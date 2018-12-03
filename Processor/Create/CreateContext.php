<?php

namespace Videni\Bundle\RestBundle\Processor\Create;

use Videni\Bundle\RestBundle\Processor\SingleItemContext;
use Videni\Bundle\RestBundle\Processor\FormContext;
use Videni\Bundle\RestBundle\Processor\FormContextTrait;

class CreateContext extends SingleItemContext implements FormContext
{
    use FormContextTrait;
}
