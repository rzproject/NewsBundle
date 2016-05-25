<?php

namespace Rz\NewsBundle\Provider;

use Sonata\CoreBundle\Validator\ErrorElement;
use Sonata\AdminBundle\Form\FormMapper;

abstract class BaseProvider implements ProviderInterface
{
    protected $name;
    protected $translator;

    /**
     * @param string                                           $name
     */
    public function __construct($name)
    {
        $this->name          = $name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getFormSettingsKeys(FormMapper $formMapper) {}

    public function buildCreateForm(FormMapper $formMapper){}

    public function buildEditForm(FormMapper $formMapper){}

    public function getTranslator()
    {
        return $this->translator;
    }

    public function setTranslator($translator)
    {
        $this->translator = $translator;
    }
}
