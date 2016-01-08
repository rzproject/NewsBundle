<?php

namespace Rz\NewsBundle\Entity;

use Rz\NewsBundle\Model\SuggestedArticles;

abstract class BaseSuggestedArticles extends SuggestedArticles
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

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return $this->getPost()->getTitle() ?: 'n/a';
    }
}
