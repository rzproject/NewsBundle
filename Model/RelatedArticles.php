<?php

namespace Rz\NewsBundle\Model;

use Sonata\ClassificationBundle\Model\CategoryInterface;
use Sonata\NewsBundle\Model\PostInterface;

abstract class RelatedArticles extends NewsRelationModel implements RelatedArticlesInterface
{
    protected $post;
    protected $relatedArticle;
    protected $position;
    protected $updatedAt;
    protected $createdAt;
    protected $enabled;

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
    public function getRelatedArticle()
    {
        return $this->relatedArticle;
    }

    /**
     * @param mixed $relatedArticle
     */
    public function setRelatedArticle(PostInterface $relatedArticle)
    {
        $this->relatedArticle = $relatedArticle;
    }
}
