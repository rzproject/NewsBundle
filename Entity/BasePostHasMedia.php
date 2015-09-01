<?php

namespace Rz\NewsBundle\Entity;

use Rz\NewsBundle\Model\PostHasMedia;

abstract class BasePostHasMedia extends PostHasMedia
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
