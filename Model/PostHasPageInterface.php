<?php
namespace Rz\NewsBundle\Model;

use Sonata\NewsBundle\Model\PostInterface;
use Sonata\PageBundle\Model\PageInterface;


interface PostHasPageInterface extends NewsRelationModelInterface
{
    /**
     * @return mixed
     */
    public function getPage();

    /**
     * @param mixed $category
     */
    public function setPage(PageInterface $page);

    /**
     * @return mixed
     */
    public function getPost();

    /**
     * @param mixed $post
     */
    public function setPost(PostInterface $post);
}