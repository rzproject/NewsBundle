<?php

namespace Rz\NewsBundle\Provider;

use Sonata\NewsBundle\Model\PostInterface;
use Sonata\AdminBundle\Validator\ErrorElement;
use Rz\NewsBundle\Provider\PostProviderInterface;

class Pool
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
    public function addProvider($name, PostProviderInterface $instance)
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
    public function addCollection($name, $provider = null, $defaultTemplate = null, $templates = array())
    {
        if (!$this->hasCollection($name)) {
            $this->collections[$name] = array(
                'default_template' => null,
                'provider' => null,
                'templates' =>array()
            );
        }

        $this->collections[$name]['default_template'] = $defaultTemplate;
        $this->collections[$name]['provider'] = $provider;
        $this->collections[$name]['templates'] = $templates;
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

    /**
     * @param string $name
     *
     * @return array
     */
    public function getTemplatesNameByCollection($name)
    {
        $collection = $this->getCollection($name);

        if (!$collection) {
            return null;
        }

        return $collection['templates'];
    }

    /**
     * @param string $name
     *
     * @return array
     */
    public function getDefaultTemplateNameByCollection($name)
    {
        $collection = $this->getCollection($name);

        if (!$collection) {
            return null;
        }

        return $collection['default_template'];
    }

    /**
     * @param string $name
     * @param string $template
     *
     * @return boolean
     */
    public function hasTemplateByCollection($name, $template = 'default')
    {
        $templates = $this->getTemplatesNameByCollection($name);
        return isset($templates[$template]);
    }

    /**
     * @param string $name
     * @param string $template
     *
     * @return array
     */
    public function getTemplateByCollection($name, $template = 'default')
    {
        $templates = $this->getTemplatesNameByCollection($name);
        if($this->hasTemplateByCollection($name, $template)) {
            $config = $templates[$template];
        } else {
            $config = array_pop($templates);
        }

        return $config;
    }

    /**
     * @return string
     */
    public function getDefaultCollection()
    {
        return $this->defaultCollection;
    }

    /**
     * @return string
     */
    public function getDefaultDefaultCollection()
    {
        return self::NEWS_POOL_DEFAULT_COLLECTION;
    }

    /**
     * @param \Sonata\AdminBundle\Validator\ErrorElement $errorElement
     * @param \Sonata\NewsBundle\Model\PostInterface   $post
     *
     * @return void
     */
    public function validate(ErrorElement $errorElement, PostInterface $post)
    {
        if (!$post->getProviderName()) {
            return;
        }

        $provider = $this->getProvider($post->getProviderName());

        $provider->validate($errorElement, $post);
    }
}
