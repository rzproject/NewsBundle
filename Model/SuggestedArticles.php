<?php

namespace Rz\NewsBundle\Model;

use Sonata\ClassificationBundle\Model\CategoryInterface;
use Sonata\NewsBundle\Model\PostInterface;



abstract class SuggestedArticles extends NewsRelationModel implements SuggestedArticlesInterface
{
    protected $position;
    protected $updatedAt;
    protected $createdAt;
    protected $enabled;
    protected $post;
    protected $suggestedArticle;

    public function __construct()
    {
        $this->position = 0;
        $this->enabled  = true;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return $this->getPost();
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
    public function getSuggestedArticle()
    {
        return $this->suggestedArticle;
    }

    /**
     * @param mixed $suggestedArticle
     */
    public function setSuggestedArticle(PostInterface $suggestedArticle)
    {
        $this->suggestedArticle = $suggestedArticle;
    }
}