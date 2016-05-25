<?php

namespace Rz\NewsBundle\Entity;

use Rz\NewsBundle\Model\PostSets;
use Rz\NewsBundle\Model\PostSetsHasPostsInterface;
use Sonata\ClassificationBundle\Model\CollectionInterface;
use Doctrine\Common\Collections\ArrayCollection;

abstract class BasePostSets extends PostSets
{
    protected $postSetsHasPosts;

    protected $collection;

    /**
     * {@inheritdoc}
     */
    public function __construct()
    {
        $this->postSetsHasPosts = new ArrayCollection();
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


    /**
     * @param mixed $postSetsHasPost
     */
    public function setPostSetsHasPosts($postSetsHasPosts)
    {
        $this->postSetsHasPosts = new ArrayCollection();
        foreach ($postSetsHasPosts as $child) {
            $this->addPostSetsHasPosts($child);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function addPostSetsHasPosts(PostSetsHasPostsInterface $postSetsHasPosts)
    {
        $postSetsHasPosts->setPostSets($this);
        $this->postSetsHasPosts[] = $postSetsHasPosts;
    }

    /**
     * @return mixed
     */
    public function getPostSetsHasPosts()
    {
        return $this->postSetsHasPosts;
    }

    /**
     * @return mixed
     */
    public function getCollection()
    {
        return $this->collection;
    }

    /**
     * @param mixed $collection
     */
    public function setCollection(CollectionInterface $collection)
    {
        $this->collection = $collection;
    }

    /**
     * {@inheritdoc}
     */
    public function removePostSetsHasPosts(PostSetsHasPostsInterface $childToDelete)
    {
        foreach ($this->getPostSetsHasPosts() as $pos => $child) {
            if ($childToDelete->getId() && $child->getId() === $childToDelete->getId()) {
                unset($this->postSetsHasPosts[$pos]);

                return;
            }

            if (!$childToDelete->getId() && $child === $childToDelete) {
                unset($this->postSetsHasPosts[$pos]);

                return;
            }
        }
    }
}