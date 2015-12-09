<?php

namespace Rz\NewsBundle\Provider;

use Sonata\CoreBundle\Validator\ErrorElement;
use Sonata\NewsBundle\Model\PostInterface;

abstract class BaseProvider implements ProviderInterface
{
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
    public function postPersist(PostInterface $object)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function postUpdate(PostInterface $object)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function validate(ErrorElement $errorElement, PostInterface $object)
    {
    }

    public function load(PostInterface $object) {
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
}
