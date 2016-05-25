<?php

namespace Rz\NewsBundle\Provider\Post;

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
    public function addCollection($name, $provider = null, $defaultTemplate = null)
    {
        if (!$this->hasCollection($name)) {
            $this->collections[$name] = array('provider' => null);
        }
        $this->collections[$name]['provider'] = $provider;
        $this->collections[$name]['default_template'] = $defaultTemplate;
    }

    public function getDefaultTemplateByCollection($name)
    {
        $collection = $this->getCollection($name);

        if (!$collection) {
            return null;
        }

        return $collection['default_template'];
    }
}
