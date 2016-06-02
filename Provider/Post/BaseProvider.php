<?php

namespace Rz\NewsBundle\Provider\Post;

use Sonata\CoreBundle\Validator\ErrorElement;
use Sonata\NewsBundle\Model\PostInterface;
use Sonata\CoreBundle\Model\ManagerInterface;
use Rz\NewsBundle\Provider\BaseProvider as Provider;

abstract class BaseProvider extends Provider
{

    protected $postManager;
    protected $categoryManager;
    protected $slugify;
    protected $postHasMediaEnabled;
    protected $suggestedArticleEnabled;
    protected $relatedArticleEnabled;

    /**
     * @param string                                           $name
     */
    public function __construct($name)
    {
        parent::__construct($name);
        $this->postHasMediaEnabled = true;
        $this->suggestedArticleEnabled = true;
        $this->relatedArticleEnabled = true;
    }

    /**
     * @param mixed $rawSettings
     */
    public function setRawSettings($rawSettings)
    {
        parent::setRawSettings($rawSettings);
        $this->setEnabledRelations();
    }

    /**
     * {@inheritdoc}
     */
    public function prePersist(PostInterface $post)
    {
        $post->setCreatedAt(new \Datetime());
        $post->setUpdatedAt(new \Datetime());
    }

    /**
     * {@inheritdoc}
     */
    public function preUpdate(PostInterface $post)
    {
        $post->setUpdatedAt(new \Datetime());
    }

    /**
     * {@inheritdoc}
     */
    public function postPersist(PostInterface $object){}

    /**
     * {@inheritdoc}
     */
    public function postUpdate(PostInterface $object){}

    /**
     * {@inheritdoc}
     */
    public function validate(ErrorElement $errorElement, PostInterface $object){}

    public function load(PostInterface $object) {}

    /**
     * @return mixed
     */
    public function getPostManager()
    {
        return $this->postManager;
    }

    /**
     * @param mixed $postManager
     */
    public function setPostManager($postManager)
    {
        $this->postManager = $postManager;
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
    public function getCategoryManager()
    {
        return $this->categoryManager;
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
     * @param mixed $categoryManager
     */
    public function setCategoryManager($categoryManager)
    {
        $this->categoryManager = $categoryManager;
    }

    protected function setEnabledRelations() {

        $params = $this->getSetting('post_has_media');
        if($params){
            $default = isset($this->defaultSettings['post_has_media']) && isset($this->defaultSettings['post_has_media']['enabled']) ? $this->defaultSettings['post_has_media']['enabled'] : true;
            $this->postHasMediaEnabled = isset($params['enable']) ? $params['enable'] : $default;
        }

        $params = $this->getSetting('related_articles');
        if($params){
            $default = isset($this->defaultSettings['related_articles']) && isset($this->defaultSettings['related_articles']['enabled']) ? $this->defaultSettings['related_articles']['enabled'] : true;
            $this->relatedArticleEnabled = isset($params['enable']) ? $params['enable'] : $default;
        }

        $params = $this->getSetting('suggested_articles');
        if($params){
            $default = isset($this->defaultSettings['suggested_articles']) && isset($this->defaultSettings['suggested_articles']['enabled']) ? $this->defaultSettings['suggested_articles']['enabled'] : true;
            $this->relatedArticleEnabled = isset($params['enable']) ? $params['enable'] : $default;
        }
    }

    public function getPostHasMediaSettings() {
        $params = $this->getSetting('post_has_media');
        $settings = [];
        if($params) {
            $settings['context'] = isset($params['context']) && $params['context'] !== null ? $params['context'] : $this->getDefaultContext();
            $settings['hide_context'] = isset($params['hide_context']) && $params['hide_context'] !== null ? $params['hide_context'] : false;

            if(isset($params['category']) && $params['category'] !== null) {
                $category = $this->categoryManager->findOneBy(array('slug'=>$this->getSlugify()->slugify($params['category']), 'context'=>$settings['context']));
                if($category) {
                    $settings['category'] = $category->getId();
                }
            }
        }
        return $settings;
    }

    public function getSuggetedArticleSettings() {
        $params = $this->getSetting('suggested_articles');
        $settings = [];
        if($params) {
            $settings['collection'] = isset($params['collection']) && $params['collection'] !== null ? $params['collection'] : null;
            $settings['hide_collection'] = isset($params['hide_collection']) && $params['hide_collection'] !== null ? $params['hide_collection'] : false;
        }
        return $settings;
    }
}
