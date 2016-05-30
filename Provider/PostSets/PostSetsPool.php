<?php

namespace Rz\NewsBundle\Provider\PostSets;

use Rz\NewsBundle\Provider\BasePool;
use Sonata\CoreBundle\Validator\ErrorElement;

class PostSetsPool extends BasePool
{
    /**
     * @param string $name
     * @param array $provider
     * @param null $defaultTemplate
     * @param array $templates
     *
     * @return void
     */
    public function addCollection($name, $provider = null, $postLookupCollection = null, $hideCollection = null)
    {
        if($this->slugify) {
            $name = $this->slugify->slugify($name);
        }

        if (!$this->hasGroup($name)) {
            $this->groups[$name] = array('provider' => null);
        }

        $this->groups[$name]['provider'] = $provider;
        if($postLookupCollection) {
            $this->groups[$name]['post_lookup_collection'] = $postLookupCollection;
        }

        if(isset($hideCollection)) {
            $this->groups[$name]['post_lookup_hide_collection'] = $hideCollection;
        }
    }

    public function getPostLookupCollectionByCollection($name)
    {
        $group = $this->getGroup($name);

        if (!$group) {
            return null;
        }

        return $group['post_lookup_collection'];
    }

    public function getPostLookupHideCollectionByCollection($name)
    {
        $group = $this->getGroup($name);

        if (!$group) {
            return null;
        }

        return $group['post_lookup_hide_collection'];
    }
}