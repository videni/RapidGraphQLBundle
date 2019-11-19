<?php

declare(strict_types=1);

namespace Videni\Bundle\RapidGraphQLBundle\Action;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Videni\Bundle\RapidGraphQLBundle\GraphQL\Resolver\DataPersister;

class CreateAction extends AbstractController
{
    private $dataPersister;

    public function __construct(DataPersister $dataPersister)
    {
        $this->dataPersister = $dataPersister;
    }

    public function __invoke(object $data)
    {
        $this->dataPersister->persist($data);

        return $data;
    }
}
