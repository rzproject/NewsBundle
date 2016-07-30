<?php

namespace Rz\NewsBundle\Provider\PostSetsHasPosts;

use Sonata\CoreBundle\Validator\ErrorElement;
use Sonata\CoreBundle\Model\ManagerInterface;
use Rz\NewsBundle\Provider\BaseProvider as Provider;
use Rz\NewsBundle\Model\PostSetsHasPostsInterface;

abstract class BaseProvider extends Provider
{

    protected $postManager;
    protected $categoryManager;
    protected $slugify;

    /**
     * {@inheritdoc}
     */
    public function prePersist(PostSetsHasPostsInterface $object)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function preUpdate(PostSetsHasPostsInterface $object)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function postPersist(PostSetsHasPostsInterface $object)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function postUpdate(PostSetsHasPostsInterface $object)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function validate(ErrorElement $errorElement, PostSetsHasPostsInterface $object)
    {
    }

    public function load(PostSetsHasPostsInterface $object)
    {
    }

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

    public function getPostSettings()
    {
        $params = $this->getSetting('post');
        $settings = [];
        if ($params) {
            $default = isset($this->defaultSettings['post']) && isset($this->defaultSettings['post']['default_collection']) ? $this->defaultSettings['post']['default_collection'] : null;
            $settings['collection'] = isset($params['collection']) && $params['collection'] !== null ? $params['collection'] : $default;

            $default = isset($this->defaultSettings['post']) && isset($this->defaultSettings['post']['hide_collection']) ? $this->defaultSettings['post']['hide_collection'] : false;
            $settings['hide_collection'] = isset($params['hide_collection']) && $params['hide_collection'] !== null ? $params['hide_collection'] : $default;
        }
        return $settings;
    }
}
