<?php

namespace Rz\NewsBundle\Entity;

use Sonata\NewsBundle\Entity\BaseCategory as BaseCategory;
use Sonata\NewsBundle\Model\CategoryInterface;
use Doctrine\Common\Collections\ArrayCollection;

abstract class Category extends BaseCategory
{


    /**
     * Required by DoctrineExtensions.
     *
     * @var mixed
     */
    protected $left;

    /**
     * Required by DoctrineExtensions.
     *
     * @var mixed
     */
    protected $right;

    /**
     * Required by DoctrineExtensions.
     *
     * @var mixed
     */
    protected $level;

    /**
     * Required by DoctrineExtensions.
     *
     * @var mixed
     */
    protected $root;

    /**
     * Parent Category.
     *
     * @var CategoryInterface
     */
    protected $parent;

    /**
     * Child Category.
     *
     * @var Collection
     */
    protected $children;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->children = new ArrayCollection();
    }


    public function getLeft()
    {
        return $this->left;
    }

    public function setLeft($left)
    {
        $this->left = $left;
    }

    public function getRight()
    {
        return $this->right;
    }

    public function setRight($right)
    {
        $this->right = $right;
    }

    public function getLevel()
    {
        return $this->level;
    }

    public function setLevel($level)
    {
        $this->level = $level;
    }

    /**
     * {@inheritdoc}
     */
    public function isRoot()
    {
        return null === $this->parent;
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * {@inheritdoc}
     */
    public function setParent(CategoryInterface $parent = null)
    {
        $this->parent = $parent;
    }

    /**
     * {@inheritdoc}
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * {@inheritdoc}
     */
    public function hasChild(CategoryInterface $category)
    {
        return $this->children->contains($category);
    }

    /**
     * {@inheritdoc}
     */
    public function addChild(CategoryInterface $category)
    {
        if (!$this->hasChild($category)) {
            $category->setParent($this);
            $this->children->add($category);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removeChild(CategoryInterface $category)
    {
        if ($this->hasChild($category)) {
            $category->setParent(null);
            $this->children->removeElement($category);
        }

        return $this;
    }
}
