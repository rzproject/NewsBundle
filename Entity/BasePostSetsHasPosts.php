<?php

namespace Rz\NewsBundle\Entity;

use Sonata\NewsBundle\Model\PostInterface;
use Rz\NewsBundle\Model\PostSetsHasPosts;

abstract class BasePostSetsHasPosts extends PostSetsHasPosts
{
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