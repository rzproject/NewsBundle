<?php

namespace Rz\NewsBundle\Provider;

use Sonata\CoreBundle\Validator\ErrorElement;
use Rz\CoreBundle\Provider\BasePool as Pool;

abstract class BasePool extends Pool
{
    public function addCollection($name, $provider = null)
    {
      $this->addGroup($name, $provider);
    }

    public function hasCollection($name)
    {
        return $this->hasGroup($name);
    }

    public function getCollection($name)
    {
        return $this->getGroup($name);
    }

    public function getCollections()
    {
        return $this->getGroups();
    }

    public function getDefaultCollection()
    {
        return $this->getDefaultGroup();
    }

    public function getProviderNameByCollection($name)
    {
       return $this->getProviderNameByGroup($name);
    }
}
