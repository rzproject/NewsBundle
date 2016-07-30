<?php

namespace Rz\NewsBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\CoreBundle\Model\ManagerInterface;
use Rz\CoreBundle\Provider\PoolInterface;

abstract class AbstractPostSetsAdmin extends Admin
{
    protected $pool;
    protected $childPool;
    protected $childeProvider;
    protected $defaultContext;
    protected $defaultCollection;
    protected $collectionManager;
    protected $contextManager;
    protected $slugify;
    protected $provider;
    protected $settings;

    /**
     * @param string $code
     * @param string $class
     * @param string $baseControllerName
     */
    public function __construct($code, $class, $baseControllerName)
    {
        parent::__construct($code, $class, $baseControllerName);
        $this->settings = [];
        $this->provider = null;
        $this->childeProvider = null;
    }

    /**
     * @return mixed
     */
    public function getSlugify()
    {
        return $this->slugify;
    }

    /**
     * @param mixed $slugify
     */
    public function setSlugify($slugify)
    {
        $this->slugify = $slugify;
    }

    /**
     * @return mixed
     */
    public function getPool()
    {
        return $this->pool;
    }

    /**
     * @param mixed $pool
     */
    public function setPool($pool)
    {
        $this->pool = $pool;
    }

    /**
     * @return mixed
     */
    public function getDefaultContext()
    {
        return $this->defaultContext;
    }

    /**
     * @param mixed $defaultContext
     */
    public function setDefaultContext($defaultContext)
    {
        $this->defaultContext = $defaultContext;
    }

    /**
     * @return mixed
     */
    public function getDefaultCollection()
    {
        return $this->defaultCollection;
    }

    /**
     * @param mixed $defaultCollection
     */
    public function setDefaultCollection($defaultCollection)
    {
        $this->defaultCollection = $defaultCollection;
    }

    /**
     * @return mixed
     */
    public function getCollectionManager()
    {
        return $this->collectionManager;
    }

    /**
     * @param mixed $collectionManager
     */
    public function setCollectionManager($collectionManager)
    {
        $this->collectionManager = $collectionManager;
    }

    /**
     * @return mixed
     */
    public function getContextManager()
    {
        return $this->contextManager;
    }

    /**
     * @param mixed $contextManager
     */
    public function setContextManager($contextManager)
    {
        $this->contextManager = $contextManager;
    }

    /**
     * @return mixed
     */
    public function getChildPool()
    {
        return $this->childPool;
    }

    /**
     * @param mixed $childPool
     */
    public function setChildPool($childPool)
    {
        $this->childPool = $childPool;
    }

    /**
     * @return null
     */
    public function getChildeProvider()
    {
        return $this->childeProvider;
    }

    /**
     * @param null $childeProvider
     */
    public function setChildeProvider($childeProvider)
    {
        $this->childeProvider = $childeProvider;
    }

    /**
     * @return mixed
     */
    public function hasChildProvider($interface = null)
    {
        if (!$interface) {
            return isset($this->childeProvider);
        }

        if ($this->childeProvider instanceof $interface) {
            return true;
        }

        return false;
    }

    /**
     * @return mixed
     */
    public function hasProvider($interface = null)
    {
        if (!$interface) {
            return isset($this->provider);
        }

        if ($this->provider instanceof $interface) {
            return true;
        }

        return false;
    }

    /**
     * @return mixed
     */
    public function getProvider()
    {
        return $this->provider;
    }

    /**
     * @param mixed $provider
     */
    public function setProvider($provider)
    {
        $this->provider = $provider;
    }

    /**
     * @return mixed
     */
    public function getSettings()
    {
        return $this->settings;
    }

    /**
     * @param mixed $settings
     */
    public function setSettings($settings)
    {
        $this->settings = $settings;
    }

    /**
     * {@inheritDoc}
     */
    public function getSetting($name, $default = null)
    {
        return isset($this->settings[$name]) ? $this->settings[$name] : $default;
    }

    /**
     * {@inheritDoc}
     */
    public function setSetting($name, $value)
    {
        $this->settings[$name] = $value;
    }
}
