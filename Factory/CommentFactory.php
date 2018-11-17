<?php

declare(strict_types=1);

namespace App\Bundle\RestBundle\Factory;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CommentFactory extends Factory
{
    private $postRepository;

    public function __construct($className, PostRepository $postRepository)
    {
        parent::__construct($className);

        $this->postRepository = $postRepository;
    }

    public function createByPostId($postId)
    {
        $comment =  $this->createNew();

        $post = $this->postRepository->find($postId);
        if (!$post) {
            throw new NotFoundHttpException(sprintf('Post %s is not found', $postId));
        }

        $comment->setPost($post);

        return $comment;
    }
}
