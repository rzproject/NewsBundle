<?php

namespace Rz\NewsBundle\Provider\PostSets;

use Sonata\AdminBundle\Form\FormMapper;
use Sonata\CoreBundle\Validator\ErrorElement;

interface ProviderInterface
{
    /**
     *
     * @param string $name
     */
    public function setName($name);

    /**
     * @return string
     */
    public function getName();

    /**
     * @return array
     */
    public function getFormSettingsKeys(FormMapper $formMapper);

    /**
     * build the related create form
     *
     * @param \Sonata\AdminBundle\Form\FormMapper $formMapper
     */
    public function buildCreateForm(FormMapper $formMapper);

    /**
     * build the related create form
     *
     * @param \Sonata\AdminBundle\Form\FormMapper $formMapper
     */
    public function buildEditForm(FormMapper $formMapper);
}
