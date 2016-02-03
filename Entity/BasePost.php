<?php

namespace Rz\NewsBundle\Entity;

use Sonata\NewsBundle\Entity\BasePost as Post;
use Sonata\NewsBundle\Model\PostInterface;
use Rz\NewsBundle\Model\PostHasCategoryInterface;
use Rz\NewsBundle\Model\PostHasMediaInterface;
use Doctrine\Common\Collections\ArrayCollection;

abstract class BasePost extends Post
{

    protected $postHasCategory;

    protected $postHasMedia;

    protected $commentsDefaultStatus = 0;

    protected $commentsEnabled = false;

    protected $settings;

	protected $viewCount;
	
	protected $needIndexer;

    const ROUTE_CLASSIFICATION_SEQ_COLLECTION = 'collection';
    const ROUTE_CLASSIFICATION_SEQ_CATEGORY = 'category';
    const ROUTE_CLASSIFICATION_SEQ_TAG = 'tag';
    const ROUTE_CLASSIFICATION_SEQ_POST = 'post';


    /**
     * {@inheritdoc}
     */
    public function __construct()
    {
        parent::__construct();

        $this->postHasCategory = new ArrayCollection();
        $this->postHasMedia = new ArrayCollection();
	    $this->viewCount = 0;
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

    /**
     * @param mixed $postHasMedia
     */
    public function setPostHasMedia($postHasMedia)
    {
        $this->postHasMedia = new ArrayCollection();
        foreach ($postHasMedia as $child) {
            $this->addPostHasMedia($child);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function addPostHasMedia(PostHasMediaInterface $postHasMedia)
    {
        $postHasMedia->setPost($this);
        $this->postHasMedia[] = $postHasMedia;
    }

    /**
     * @return mixed
     */
    public function getPostHasMedia()
    {
        return $this->postHasMedia;
    }

    /**
     * {@inheritdoc}
     */
    public function removePostHasMedia(PostHasMediaInterface $childToDelete)
    {
        foreach ($this->getPostHasMedia() as $pos => $child) {
            if ($childToDelete->getId() && $child->getId() === $childToDelete->getId()) {
                unset($this->postHasMedia[$pos]);

                return;
            }

            if (!$childToDelete->getId() && $child === $childToDelete) {
                unset($this->postHasMedia[$pos]);

                return;
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getSettings()
    {
        return $this->settings;
    }

    /**
     * {@inheritDoc}
     */
    public function getSetting($name, $default = null)
    {
        return isset($this->settings[$name]) ? $this->settings[$name] : $default;
    }

    /**
     * {@inheritDoc}
     */
    public function setSetting($name, $value)
    {
        $this->settings[$name] = $value;
    }

    public function setSettings($settings)
    {
        $this->settings = $settings;
    }

	/**
	 * @return int
	 */
	public function getViewCount()
	{
		return $this->viewCount;
	}

	/**
	 * @param int $viewCount
	 */
	public function setViewCount($viewCount)
	{
		$this->viewCount = $viewCount;
	}

	public function incrementViewCount() {
		$this->viewCount++;
	}
	
	public function setNeedIndexer($needIndexer = true){
		$this->needIndexer = $needIndexer;
		return $this;
	}
	
	public function getNeedIndexer(){
		return $this->needIndexer;
	}
}
