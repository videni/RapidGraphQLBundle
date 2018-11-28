<?php

declare(strict_types=1);

namespace Videni\Bundle\RestBundle\Handler;

use Doctrine\Common\Persistence\ObjectManager;

interface DeleteHandlerInterface
{
    /**
     * Handle delete entity object.
     *
     * @param mixed            $id
     * @param ObjectManager $manager
     */
    public function handleDelete($id, ObjectManager $manager);
}
