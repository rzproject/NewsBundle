<?php

namespace Rz\NewsBundle\Provider\PostSets;

use Sonata\CoreBundle\Validator\ErrorElement;
use Rz\NewsBundle\Model\PostSetsInterface;

abstract class BasePostSetsProvider implements ProviderInterface
{
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
    public function postPersist(PostSetsInterface $object)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function postUpdate(PostSetsInterface $object)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function validate(ErrorElement $errorElement, PostSetsInterface $object)
    {
    }

    public function load(PostSetsInterface $object) {
    }
}
