<?php
namespace Rz\NewsBundle\Model;

use Sonata\NewsBundle\Model\PostInterface;
use Sonata\MediaBundle\Model\MediaInterface;


interface PostHasMediaInterface extends NewsRelationModelInterface
{
    /**
     * @return mixed
     */
    public function getMedia();

    /**
     * @param mixed $category
     */
    public function setMedia(MediaInterface $category);

    /**
     * @return mixed
     */
    public function getPost();

    /**
     * @param mixed $post
     */
    public function setPost(PostInterface $post);
}