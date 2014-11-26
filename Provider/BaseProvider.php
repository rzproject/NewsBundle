<?php

namespace Rz\NewsBundle\Provider;

use Sonata\AdminBundle\Validator\ErrorElement;
use Sonata\NewsBundle\Model\PostInterface;

abstract class BaseProvider implements PostProviderInterface
{
    protected $templates = array();

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
    public function validate(ErrorElement $errorElement, PostInterface $post)
    {

    }
}
