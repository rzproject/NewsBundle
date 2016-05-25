<?php

namespace Rz\NewsBundle\Entity;

use Rz\NewsBundle\Model\NewsSnapshot;
use Sonata\NewsBundle\Model\PostInterface;

abstract class BaseNewsSnapshot extends NewsSnapshot
{

    protected $post;

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

    /**
     * Pre Persist method
     */
    public function prePersist()
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    /**
     * Pre Update method
     */
    public function preUpdate()
    {
        $this->updatedAt = new \DateTime();
    }
}