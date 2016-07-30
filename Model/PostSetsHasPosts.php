<?php

namespace Rz\NewsBundle\Model;

use Sonata\NewsBundle\Model\PostInterface;

abstract class PostSetsHasPosts extends NewsRelationModel implements PostSetsHasPostsInterface
{
    protected $postSets;

    protected $post;

    protected $publicationDateStart;

    protected $settings;

    public function __construct()
    {
        $this->position = 0;
        $this->enabled  = true;

        $this->setPublicationDateStart(new \DateTime());
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return $this->getPostSets().' | '.$this->getPost();
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

    /**
     * @return mixed
     */
    public function getPostSets()
    {
        return $this->postSets;
    }

    /**
     * @param mixed $postSets
     */
    public function setPostSets(PostSetsInterface $postSets)
    {
        $this->postSets = $postSets;
    }

    /**
     * @return mixed
     */
    public function getPublicationDateStart()
    {
        return $this->publicationDateStart;
    }

    /**
     * @param mixed $publicationDateStart
     */
    public function setPublicationDateStart($publicationDateStart)
    {
        $this->publicationDateStart = $publicationDateStart;
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
}
