<?php

namespace Rz\NewsBundle\Provider;

use Sonata\AdminBundle\Form\FormMapper;
use Rz\CoreBundle\Provider\BaseProvider as Provider;

abstract class BaseProvider extends Provider
{
    public function getFormSettingsKeys(FormMapper $formMapper) {}

    public function buildCreateForm(FormMapper $formMapper){}

    public function buildEditForm(FormMapper $formMapper){}
}
