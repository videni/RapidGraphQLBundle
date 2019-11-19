<?php
declare(strict_types=1);

namespace Pintushi\Bundle\RapidGraphQLBundle\Action;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Videni\Bundle\RapidGraphQLBundle\GraphQL\Resolver\DataPersister;

class DeleteAction extends AbstractController
{
    private $dataPersister;

    public function __construct(DataPersister $dataPersister)
    {
        $this->dataPersister = $dataPersister;
    }

    public function __invoke(object $data)
    {
        $this->dataPersister->remove($data);

        return $data;
    }
}
