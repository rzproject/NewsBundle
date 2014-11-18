<?php

namespace Rz\NewsBundle\Model;

use Sonata\ClassificationBundle\Model\CategoryInterface;
use Sonata\NewsBundle\Model\PostInterface;



abstract class PostHasCategory extends NewsRelationModel implements PostHasCategoryInterface
{
    protected $category;

    protected $post;

    public function __construct()
    {
        $this->position = 0;
        $this->enabled  = false;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return $this->getCategory().' | '.$this->getPost();
    }

    /**
     * @return mixed
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param mixed $category
     */
    public function setCategory(CategoryInterface $category)
    {
        $this->category = $category;
    }

    /**
     * @return mixed
     */
    public function getPost()
    {
        return $this->post;
    }

    /**
     * @param mixed $post
     */
    public function setPost(PostInterface $post)
    {
        $this->post = $post;
    }
}