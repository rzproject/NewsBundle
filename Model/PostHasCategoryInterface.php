<?php
namespace Rz\NewsBundle\Model;

use Sonata\ClassificationBundle\Model\CategoryInterface;
use Sonata\NewsBundle\Model\PostInterface;


interface PostHasCategoryInterface extends NewsRelationModelInterface
{
    /**
     * @return mixed
     */
    public function getCategory();

    /**
     * @param mixed $category
     */
    public function setCategory(CategoryInterface $category);

    /**
     * @return mixed
     */
    public function getPost();

    /**
     * @param mixed $post
     */
    public function setPost(PostInterface $post);
}