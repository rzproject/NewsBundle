<?php
namespace Rz\NewsBundle\Model;

use Sonata\ClassificationBundle\Model\CategoryInterface;
use Sonata\NewsBundle\Model\PostInterface;

interface SuggestedArticlesInterface extends NewsRelationModelInterface
{
    /**
     * @return mixed
     */
    public function getPost();

    /**
     * @param mixed $post
     */
    public function setPost(PostInterface $post);
}
