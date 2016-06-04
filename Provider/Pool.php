<?php

namespace Rz\NewsBundle\Provider;

use Sonata\CoreBundle\Validator\ErrorElement;
use Rz\NewsBundle\Provider\BasePool;

class Pool extends BasePool
{
    /**
     * @param string $name
     * @param array $provider
     * @param null $defaultTemplate
     * @param array $templates
     *
     * @return void
     */
    public function addCollection($name, $provider = null, $settings = null)
    {
        if($this->slugify) {
            $name = $this->slugify->slugify($name);
        }

        if (!$this->hasGroup($name)) {
            $this->groups[$name] = array('provider' => null);
        }

        $this->groups[$name]['provider'] = $provider;
        $this->groups[$name]['settings'] = $settings;
    }

    public function getSettingsByCollection($name)
    {
        $group = $this->getGroup($name);

        if (!$group) {
            return null;
        }

        return $group['settings'];
    }
}
