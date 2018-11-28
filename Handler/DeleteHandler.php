<?php

declare(strict_types=1);

namespace Videni\Bundle\RestBundle\Handler;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityNotFoundException;
use Oro\Bundle\SecurityBundle\Exception\ForbiddenException;

/**
 * A class encapsulates a business logic responsible to delete entity
 */
class DeleteHandler implements DeleteHandlerInterface
{
    /**
     * Handle delete entity object.
     *
     * @param mixed            $id
     * @param ObjectManager $manager
     * @throws EntityNotFoundException if an entity with the given id does not exist
     * @throws ForbiddenException if a delete operation is forbidden
     */
    public function handleDelete($id, ObjectManager $manager)
    {
        $entity = $manager->find($id);
        if (!$entity) {
            throw new EntityNotFoundException();
        }

        $this->processDelete($entity, $manager);
    }

    /**
     * Deletes given entity object.
     *
     * @param object        $entity
     * @param ObjectManager $em
     */
    public function processDelete($entity, ObjectManager $em)
    {
        $this->checkPermissions($entity, $em);
        $this->deleteEntity($entity, $em);
        $em->flush();
    }

    /**
     * Checks if a delete operation is allowed
     *
     * @param object        $entity
     * @param ObjectManager $em
     * @throws ForbiddenException if a delete operation is forbidden
     */
    protected function checkPermissions($entity, ObjectManager $em)
    {
    }

    /**
     * Deletes the given entity
     *
     * @param object        $entity
     * @param ObjectManager $em
     */
    protected function deleteEntity($entity, ObjectManager $em)
    {
        $em->remove($entity);
    }
}
