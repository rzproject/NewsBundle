<?php
namespace Rz\NewsBundle\Model;

use Sonata\NewsBundle\Model\PostInterface;

interface PostSetsHasPostsInterface extends NewsRelationModelInterface
{
    /**
     * @return mixed
     */
    public function getPostSets();

    /**
     * @param mixed $postSets
     */
    public function setPostSets(PostSetsInterface $postSets);

    /**
     * @return mixed
     */
    public function getPost();

    /**
     * @param mixed $post
     */
    public function setPost(PostInterface $post);
}
