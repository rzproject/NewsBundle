<?php

namespace Rz\NewsBundle\Entity;

use Sonata\NewsBundle\Entity\BasePost as Post;
use Doctrine\Common\Collections\ArrayCollection;
use Rz\NewsBundle\Model\PostHasCategoryInterface;
use Rz\NewsBundle\Model\PostHasMediaInterface;
use Rz\NewsBundle\Model\RelatedArticlesInterface;
use Rz\NewsBundle\Model\SuggestedArticlesInterface;
use Rz\NewsBundle\Model\PostHasPageInterface;


abstract class BasePost extends Post
{
    protected $commentsDefaultStatus = true;
    protected $settings;
    protected $seoSettings;
    protected $postHasCategory;
    protected $postHasMedia;
    protected $relatedArticles;
    protected $suggestedArticles;
    protected $postHasPage;
    protected $site;
    protected $publicationDateEnd;

    /**
     * {@inheritdoc}
     */
    public function __construct()
    {
        parent::__construct();

        $this->postHasCategory = new ArrayCollection();
        $this->postHasMedia = new ArrayCollection();
        $this->relatedArticles = new ArrayCollection();
        $this->suggestedArticles = new ArrayCollection();
        $this->postHasPage = new ArrayCollection();
    }

    /**
     * @return mixed
     */
    public function getSeoSettings()
    {
        return $this->seoSettings;
    }

    /**
     * @param mixed $settings
     */
    public function setSeoSettings($seoSettings)
    {
        $this->seoSettings = $seoSettings;
    }

    /**
     * {@inheritDoc}
     */
    public function getSeoSetting($name, $default = null)
    {
        return isset($this->seoSettings[$name]) ? $this->seoSettings[$name] : $default;
    }

    /**
     * {@inheritDoc}
     */
    public function setSeoSetting($name, $value)
    {
        $this->seoSettings[$name] = $value;
    }

    /**
     * @return mixed
     */
    public function getSettings()
    {
        return $this->settings;
    }

    /**
     * @param mixed $settings
     */
    public function setSettings($settings)
    {
        $this->settings = $settings;
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

    public function setRelatedArticles($relatedArticles)
    {
        $this->relatedArticles = new ArrayCollection();
        foreach ($relatedArticles as $child) {
            $this->addRelatedArticle($child);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function addRelatedArticle(RelatedArticlesInterface $relatedArticle)
    {
        $relatedArticle->setPost($this);
        $this->relatedArticles[] = $relatedArticle;
    }

    /**
     * @return mixed
     */
    public function getRelatedArticles()
    {
        return $this->relatedArticles;
    }

    /**
     * {@inheritdoc}
     */
    public function removeRelatedArticle(RelatedArticlesInterface $childToDelete)
    {
        foreach ($this->getRelatedArticles() as $pos => $child) {
            if ($childToDelete->getId() && $child->getId() === $childToDelete->getId()) {
                unset($this->relatedArticles[$pos]);

                return;
            }

            if (!$childToDelete->getId() && $child === $childToDelete) {
                unset($this->relatedArticles[$pos]);

                return;
            }
        }
    }


    public function setSuggestedArticles($suggestedArticles)
    {
        $this->suggestedArticles = new ArrayCollection();
        foreach ($suggestedArticles as $child) {
            $this->addSuggestedArticle($child);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function addSuggestedArticle(SuggestedArticlesInterface $suggestedArticle)
    {
        $suggestedArticle->setPost($this);
        $this->suggestedArticles[] = $suggestedArticle;
    }

    /**
     * @return mixed
     */
    public function getSuggestedArticles()
    {
        return $this->suggestedArticles;
    }

    /**
     * {@inheritdoc}
     */
    public function removeSuggestedArticle(SuggestedArticlesInterface $childToDelete)
    {
        foreach ($this->getSuggestedArticles() as $pos => $child) {
            if ($childToDelete->getId() && $child->getId() === $childToDelete->getId()) {
                unset($this->suggestedArticles[$pos]);

                return;
            }

            if (!$childToDelete->getId() && $child === $childToDelete) {
                unset($this->suggestedArticles[$pos]);

                return;
            }
        }
    }

    /**
     * @param mixed $postHasPage
     */
    public function setPostHasPage($postHasPage)
    {
        $this->postHasPage = new ArrayCollection();
        foreach ($postHasPage as $child) {
            $this->addPostHasPage($child);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function addPostHasPage(PostHasPageInterface $postHasPage)
    {
        $postHasPage->setPost($this);
        $this->postHasPage[] = $postHasPage;
    }

    /**
     * @return mixed
     */
    public function getPostHasPage()
    {
        return $this->postHasPage;
    }

    /**
     * {@inheritdoc}
     */
    public function removePostHasPage(PostHasPageInterface $childToDelete)
    {
        foreach ($this->getPostHasPage() as $pos => $child) {
            if ($childToDelete->getId() && $child->getId() === $childToDelete->getId()) {
                unset($this->postHasPage[$pos]);

                return;
            }

            if (!$childToDelete->getId() && $child === $childToDelete) {
                unset($this->postHasPage[$pos]);

                return;
            }
        }
    }

    public function isNew() {
        if ($this->getId()) {
            return false;
        }
        return true;
    }

    /**
     * @return mixed
     */
    public function getSite()
    {
        return $this->site;
    }

    /**
     * @param mixed $site
     */
    public function setSite($site)
    {
        $this->site = $site;
    }

    /**
     * @return mixed
     */
    public function getPublicationDateEnd()
    {
        return $this->publicationDateEnd;
    }

    /**
     * @param mixed $publicationDateEnd
     */
    public function setPublicationDateEnd($publicationDateEnd)
    {
        $this->publicationDateEnd = $publicationDateEnd;
    }
}
