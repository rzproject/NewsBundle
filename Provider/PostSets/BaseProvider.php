<?php

namespace Rz\NewsBundle\Provider\PostSets;

use Sonata\CoreBundle\Validator\ErrorElement;
use Sonata\CoreBundle\Model\ManagerInterface;
use Rz\NewsBundle\Provider\BaseProvider as Provider;
use Rz\NewsBundle\Model\PostSetsInterface;

abstract class BaseProvider extends Provider
{

    protected $postSetsManager;
    protected $categoryManager;
    protected $slugify;

    /**
     * @param string                                           $name
     */
    public function __construct($name)
    {
        parent::__construct($name);
    }

    /**
     * @param mixed $rawSettings
     */
    public function setRawSettings($rawSettings)
    {
        parent::setRawSettings($rawSettings);
    }

    /**
     * {@inheritdoc}
     */
    public function prePersist(PostSetsInterface $post)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function preUpdate(PostSetsInterface $post)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function postPersist(PostSetsInterface $object){}

    /**
     * {@inheritdoc}
     */
    public function postUpdate(PostSetsInterface $object){}

    /**
     * {@inheritdoc}
     */
    public function validate(ErrorElement $errorElement, PostSetsInterface $object){}

    public function load(PostSetsInterface $object) {}

    /**
     * @return mixed
     */
    public function getCategoryManager()
    {
        return $this->categoryManager;
    }

    /**
     * @return mixed
     */
    public function getSlugify()
    {
        return $this->slugify;
    }

    /**
     * @param mixed $slugify
     */
    public function setSlugify($slugify)
    {
        $this->slugify = $slugify;
    }

    /**
     * @param mixed $categoryManager
     */
    public function setCategoryManager($categoryManager)
    {
        $this->categoryManager = $categoryManager;
    }

    /**
     * @return mixed
     */
    public function getPostSetsManager()
    {
        return $this->postSetsManager;
    }

    /**
     * @param mixed $postSetsManager
     */
    public function setPostSetsManager($postSetsManager)
    {
        $this->postSetsManager = $postSetsManager;
    }
}
