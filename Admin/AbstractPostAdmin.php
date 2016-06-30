<?php

namespace Rz\NewsBundle\Admin;

use Sonata\NewsBundle\Admin\PostAdmin as Admin;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\CoreBundle\Model\ManagerInterface;
use Rz\CoreBundle\Provider\PoolInterface;
use Rz\CoreBundle\Admin\AdminProviderInterface;


abstract class AbstractPostAdmin extends Admin
{
    protected $collectionManager;
    protected $contextManager;
    protected $categoryManager;
    protected $tagManager;
    protected $pool;
    protected $defaultContext;
    protected $defaultCollection;
    protected $slugify;
    protected $securityTokenStorage;
    protected $postHasMediaEnabled;
    protected $suggestedArticleEnabled;
    protected $relatedArticleEnabled;
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
        $this->postHasMediaEnabled = true;
        $this->suggestedArticleEnabled = true;
        $this->relatedArticleEnabled = true;
        $this->settings = [];
        $this->provider = null;
    }

    /**
     * {@inheritdoc}
     */
    public function getForm()
    {
        $this->setEnabledRelations();
        return parent::getForm();
    }

    /**
     * @return mixed
     */
    public function getCollectionManager()
    {
        return $this->collectionManager;
    }

    /**
     * @param \Sonata\CoreBundle\Model\ManagerInterface $collectionManager
     */
    public function setCollectionManager(ManagerInterface $collectionManager)
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
     * @param \Sonata\CoreBundle\Model\ManagerInterface $contextManager
     */
    public function setContextManager(ManagerInterface $contextManager)
    {
        $this->contextManager = $contextManager;
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
    public function getTagManager()
    {
        return $this->tagManager;
    }

    /**
     * @param mixed $tagManager
     */
    public function setTagManager($tagManager)
    {
        $this->tagManager = $tagManager;
    }

    /**
     * @return mixed
     */
    public function getCategoryManager()
    {
        return $this->categoryManager;
    }

    /**
     * @param mixed $categoryManager
     */
    public function setCategoryManager($categoryManager)
    {
        $this->categoryManager = $categoryManager;
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
    public function getDefaultContext()
    {
        return $this->defaultContext;

    }

    /**
     * @param mixed $defaultContext
     */
    public function setDefaultContext($defaultContext)
    {
        $this->defaultContext = $this->getSlugify()->slugify($defaultContext);
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
    public function getPostHasMediaEnabled()
    {
        return $this->postHasMediaEnabled;
    }

    /**
     * @param mixed $postHasMediaEnabled
     */
    public function setPostHasMediaEnabled($postHasMediaEnabled)
    {
        $this->postHasMediaEnabled = $postHasMediaEnabled;
    }

    /**
     * @return mixed
     */
    public function getSuggestedArticleEnabled()
    {
        return $this->suggestedArticleEnabled;
    }

    /**
     * @param mixed $suggestedArticleEnabled
     */
    public function setSuggestedArticleEnabled($suggestedArticleEnabled)
    {
        $this->suggestedArticleEnabled = $suggestedArticleEnabled;
    }

    /**
     * @return mixed
     */
    public function getRelatedArticleEnabled()
    {
        return $this->relatedArticleEnabled;
    }

    /**
     * @param mixed $relatedArticleEnabled
     */
    public function setRelatedArticleEnabled($relatedArticleEnabled)
    {
        $this->relatedArticleEnabled = $relatedArticleEnabled;
    }

    /**
     * @return mixed
     */
    public function getSecurityTokenStorage()
    {
        return $this->securityTokenStorage;
    }

    /**
     * @param mixed $securityTokenStorage
     */
    public function setSecurityTokenStorage($securityTokenStorage)
    {
        $this->securityTokenStorage = $securityTokenStorage;
    }

    public function getCurrentUser() {
        return $this->getSecurityTokenStorage()->getToken()->getUser();
    }

    /**
     * @return mixed
     */
    public function hasProvider($interface = null)
    {
        if(!$interface) {
            return isset($this->provider);
        }

        if($this->provider instanceof $interface) {
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

    protected function setEnabledRelations() {
        if($this->settings) {
            $this->postHasMediaEnabled = isset($this->settings['post_has_media']) && isset($this->settings['post_has_media']['enabled']) ? $this->settings['post_has_media']['enabled'] : false;
            $this->relatedArticleEnabled = isset($this->settings['related_articles']) && isset($this->settings['related_articles']['enabled']) ? $this->settings['related_articles']['enabled'] : false;
            $this->suggestedArticleEnabled = isset($this->settings['suggested_articles']) && isset($this->settings['suggested_articles']['enabled']) ? $this->settings['suggested_articles']['enabled'] : false;
        }
    }

    public function getPostHasMediaSettings() {
        $params = $this->getSetting('post_has_media');
        $settings = [];
        $settings['context'] = isset($params['default_context']) && $params['default_context'] !== null ? $params['default_context'] : $this->getDefaultContext();
        $settings['hide_context'] = isset($params['hide_context']) && $params['hide_context'] !== null ? $params['hide_context'] : false;

        if(isset($params['default_category']) && $params['default_category'] !== null) {
            $category = $this->categoryManager->findOneBy(array('slug'=>$this->getSlugify()->slugify($params['default_category']), 'context'=>$settings['context']));
            if($category) {
                $settings['category'] = $category->getId();
            }
        }
        return $settings;
    }

    public function getSuggetedArticleSettings() {
        $params = $this->getSetting('suggested_articles');
        $settings = [];
        if($params) {
            $settings['collection'] = isset($params['default_collection']) && $params['default_collection'] !== null ? $params['default_collection'] : null;
            $settings['hide_collection'] = isset($params['hide_collection']) && $params['hide_collection'] !== null ? $params['hide_collection'] : false;
        }
        return $settings;
    }

    public function getRelatedArticleSettings() {
        $params = $this->getSetting('related_articles');
        $settings = [];
        if($params) {
            $settings['collection'] = isset($params['default_collection']) && $params['default_collection'] !== null ? $params['default_collection'] : null;
            $settings['hide_collection'] = isset($params['hide_collection']) && $params['hide_collection'] !== null ? $params['hide_collection'] : false;
        }
        return $settings;
    }

    public function getPostHasCagegorySettings() {
        $params = $this->getSetting('post_has_category');
        $settings = [];
        if($params) {
            $settings['context'] = isset($params['default_context']) && $params['default_context'] !== null ? $params['default_context'] : $this->getDefaultContext();
        }
        return $settings;
    }

    public function getTagsSettings() {
        $params = $this->getSetting('tags');
        $settings = [];
        if($params) {
            $settings['context'] = isset($params['default_context']) && $params['default_context'] !== null ? $params['default_context'] : $this->getDefaultContext();
        }
        return $settings;
    }

    public function getMediaSettings() {
        $params = $this->getSetting('media');
        $settings = [];
        $settings['context'] = isset($params['default_context']) && $params['default_context'] !== null ? $params['default_context'] : $this->getDefaultContext();
        $settings['hide_context'] = isset($params['hide_context']) && $params['hide_context'] !== null ? $params['hide_context'] : false;

        if(isset($params['default_category']) && $params['default_category'] !== null) {
            $category = $this->categoryManager->findOneBy(array('slug'=>$this->getSlugify()->slugify($params['default_category']), 'context'=>$settings['context']));
            if($category) {
                $settings['category'] = $category->getId();
            }
        }
        return $settings;
    }
}
