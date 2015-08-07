<?php

namespace Rz\NewsBundle\Provider;

use Sonata\CoreBundle\Validator\ErrorElement;
use Sonata\NewsBundle\Model\PostInterface;

abstract class BaseProvider implements PostProviderInterface
{
    protected $templates = array();
    protected $metatagChoices = array();
    protected $postManager;

    /**
     * @param string                                           $name
     */
    public function __construct($name)
    {
        $this->name          = $name;
    }

    /**
     * {@inheritdoc}
     */
    public function preRemove(PostInterface $post)
    {

    }

    /**
     * {@inheritdoc}
     */
    public function postRemove(PostInterface $post)
    {

    }

    /**
     * {@inheritdoc}
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function setTemplates(array $templates)
    {
        $this->templates = $templates;
    }

    /**
     *
     * @return array
     */
    public function getTemplates()
    {
        return $this->templates;
    }

    /**
     * {@inheritdoc}
     */
    public function getTemplateChoices()
    {
        $list = array();
        foreach($this->templates as $key=>$value) {
            $list[$value['path']] = $value['name'].' - '.$value['path'];
        }
        return $list;
    }

    public function getTemplatePath($name)
    {
        $template = $this->getTemplate($name);
        if($template) {
            return $template['path'];
        } else {
            return;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getTemplate($name)
    {
        return isset($this->templates[$name]) ? $this->templates[$name] : null;
    }

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
    public function postPersist(PostInterface $post)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function postUpdate(PostInterface $post)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function validate(ErrorElement $errorElement, PostInterface $post)
    {

    }

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

    public function load(PostInterface $post) {

    }

    /**
     * @return array
     */
    public function getMetatagChoices()
    {
        return $this->metatagChoices;
    }

    /**
     * @param array $metatagChoices
     */
    public function setMetatagChoices($metatagChoices)
    {
        $this->metatagChoices = $metatagChoices;
    }
}
