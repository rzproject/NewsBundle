<?php

namespace Rz\NewsBundle\Entity;

use Sonata\NewsBundle\Entity\BasePost as BasePost;
use Sonata\NewsBundle\Model\PostInterface;
use Rz\NewsBundle\Model\PostHasCategoryInterface;
use Doctrine\Common\Collections\ArrayCollection;

abstract class Post extends BasePost
{

    protected $postHasCategory;

    /**
     * {@inheritdoc}
     */
    public function __construct()
    {
        parent::__construct();

        $this->postHasCategory = new ArrayCollection();
    }


    /**
     * @param mixed $postHasCategory
     */
    public function setPostHasCategory($postHasCategory)
    {
        $this->postHasCategory = new ArrayCollection();
        foreach ($postHasCategory as $child) {
            $this->addPostHasCategory($child);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function addPostHasCategory(PostHasCategoryInterface $postHasCategory)
    {
        $postHasCategory->setPost($this);
        $this->postHasCategory[] = $postHasCategory;
    }

    /**
     * @return mixed
     */
    public function getPostHasCategory()
    {
        return $this->postHasCategory;
    }

    /**
     * {@inheritdoc}
     */
    public function removePostHasCategory(PostHasCategoryInterface $childToDelete)
    {
        foreach ($this->getPostHasCategory() as $pos => $child) {
            if ($childToDelete->getId() && $child->getId() === $childToDelete->getId()) {
                unset($this->postHasCategory[$pos]);

                return;
            }

            if (!$childToDelete->getId() && $child === $childToDelete) {
                unset($this->postHasCategory[$pos]);

                return;
            }
        }
    }
}
