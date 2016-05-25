<?php

namespace Rz\NewsBundle\Provider\Post;

use Sonata\CoreBundle\Validator\ErrorElement;
use Sonata\NewsBundle\Model\PostInterface;
use Rz\NewsBundle\Provider\BaseProvider as Provider;

abstract class BaseProvider extends Provider
{
    protected $templates = [];
    protected $defaultTemplate;
    protected $postManager;
    protected $isControllerEnabled;

    /**
     * {@inheritdoc}
     */
    public function prePersist(PostInterface $post)
    {
        $post->setCreatedAt(new \Datetime());
        $post->setUpdatedAt(new \Datetime());
    }

    /**
     * {@inheritdoc}
     */
    public function preUpdate(PostInterface $post)
    {
        $post->setUpdatedAt(new \Datetime());
    }


    /**
     * {@inheritdoc}
     */
    public function postPersist(PostInterface $object){}

    /**
     * {@inheritdoc}
     */
    public function postUpdate(PostInterface $object){}

    /**
     * {@inheritdoc}
     */
    public function validate(ErrorElement $errorElement, PostInterface $object){}

    public function load(PostInterface $object) {}

    /**
     * @return mixed
     */
    public function getPostManager()
    {
        return $this->postManager;
    }

    /**
     * @param mixed $postManager
     */
    public function setPostManager($postManager)
    {
        $this->postManager = $postManager;
    }

    /**
     * @return array
     */
    public function getTemplates()
    {
        return $this->templates;
    }

    /**
     * @param array $templates
     */
    public function setTemplates($templates)
    {
        $this->templates = $templates;
    }

    /**
     * @return mixed
     */
    public function getIsControllerEnabled()
    {
        return $this->isControllerEnabled;
    }

    /**
     * @param mixed $isControllerEnabled
     */
    public function setIsControllerEnabled($isControllerEnabled)
    {
        $this->isControllerEnabled = $isControllerEnabled;
    }

    /**
     * @return mixed
     */
    public function getDefaultTemplate()
    {
        return $this->defaultTemplate;
    }

    /**
     * @param mixed $defaultTemplate
     */
    public function setDefaultTemplate($defaultTemplate)
    {
        $this->defaultTemplate = $defaultTemplate;
    }

    public function getPreferedChoice() {
        $template = $this->getDefaultTemplate() ?: null;
        if($template) {
            return array($template);
        }
        return [];
    }
}
