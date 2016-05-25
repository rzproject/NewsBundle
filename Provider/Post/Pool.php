<?php

namespace Rz\NewsBundle\Provider\Post;

use Sonata\CoreBundle\Validator\ErrorElement;

class Pool implements PoolInterface
{
    /**
     * @var array
     */
    protected $providers = array();

    protected $collections = array();

    protected $defaultCollection;

    const NEWS_POOL_DEFAULT_COLLECTION = 'default';

    /**
     * @param string $collection
     */
    public function __construct($collection)
    {
        $this->defaultCollection = $collection;
    }

    /**
     * @throws \RuntimeException
     *
     * @param string $name
     *
     * @return \Sonata\MediaBundle\Provider\MediaProviderInterface
     */
    public function getProvider($name)
    {
        if (!isset($this->providers[$name])) {
            throw new \RuntimeException(sprintf('unable to retrieve the provider named : `%s`', $name));
        }

        return $this->providers[$name];
    }

    /**
     * @param string                 $name
     * @param PostProviderInterface $instance
     *
     * @return void
     */
    public function addProvider($name, ProviderInterface $instance)
    {
        $this->providers[$name] = $instance;
    }

    /**
     * @param array $providers
     *
     * @return void
     */
    public function setProviders($providers)
    {
        $this->providers = $providers;
    }

    /**
     * @return \Rz\NewsBundle\Provider\PostProviderInterface[]
     */
    public function getProviders()
    {
        return $this->providers;
    }

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

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasCollection($name)
    {
        return isset($this->collections[$name]);
    }

    /**
     * @param string $name
     *
     * @return array|null
     */
    public function getCollection($name)
    {
        if (!$this->hasCollection($name)) {
            return null;
        }

        return $this->collections[$name];
    }

    /**
     * Returns the collection list
     *
     * @return array
     */
    public function getCollections()
    {
        return $this->collections;
    }

    /**
     * @return string
     */
    public function getDefaultCollection()
    {
        return $this->defaultCollection;
    }

    /**
     * @param string $name
     *
     * @return array
     */
    public function getProviderNameByCollection($name)
    {
        $collection = $this->getCollection($name);

        if (!$collection) {
            return null;
        }

        return $collection['provider'];
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
