<?php

namespace Rz\NewsBundle\Provider\Post;

interface PoolInterface
{
    /**
     * @throws \RuntimeException
     *
     * @param string $name
     *
     * @return \Sonata\MediaBundle\Provider\MediaProviderInterface
     */
    public function getProvider($name);

    /**
     * @param string                 $name
     * @param PostProviderInterface $instance
     *
     * @return void
     */
    public function addProvider($name, ProviderInterface $instance);

    /**
     * @param array $providers
     *
     * @return void
     */
    public function setProviders($providers);

    /**
     * @return \Rz\NewsBundle\Provider\PostProviderInterface[]
     */
    public function getProviders();

    /**
     * @param string $name
     * @param array $provider
     * @param null $defaultTemplate
     * @param array $templates
     *
     * @return void
     */
    public function addCollection($name, $provider = null);

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasCollection($name);

    /**
     * @param string $name
     *
     * @return array|null
     */
    public function getCollection($name);

    /**
     * Returns the collection list
     *
     * @return array
     */
    public function getCollections();

    /**
     * @param string $name
     *
     * @return array
     */
    public function getProviderNameByCollection($name);
}
