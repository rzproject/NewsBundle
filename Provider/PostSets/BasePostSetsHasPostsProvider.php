<?php

namespace Rz\NewsBundle\Provider\PostSets;

use Sonata\CoreBundle\Validator\ErrorElement;
use Rz\NewsBundle\Model\PostSetsHasPostsInterface;

abstract class BasePostSetsHasPostsProvider implements ProviderInterface
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

    public function load(PostSetsHasPostsInterface $object) {
    }
}
