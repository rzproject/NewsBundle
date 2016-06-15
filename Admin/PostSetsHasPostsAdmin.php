<?php


namespace Rz\NewsBundle\Admin;

use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Route\RouteCollection;
use Rz\CoreBundle\Provider\PoolInterface;
use Rz\CoreBundle\Admin\AdminProviderInterface;
use Sonata\CoreBundle\Validator\ErrorElement;

class PostSetsHasPostsAdmin extends AbstractPostSetsHasPostsAdmin implements AdminProviderInterface
{
    protected $parentAssociationMapping = 'postSets';
    protected $formOptions = array('cascade_validation'=>true);

    /**
     * {@inheritdoc}
     */
    public function setSubject($subject)
    {
        parent::setSubject($subject);
        $this->provider = $this->getPoolProvider($this->getPool());
    }

    /**
     * @param \Sonata\AdminBundle\Form\FormMapper $formMapper
     *
     * @return void
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        if($this->hasProvider()) {
            $formMapper
                ->tab('tab.rz_news_post_sets_has_posts')
                    ->with('tab.group.rz_news_post_sets_has_posts', array('class' => 'col-md-12'))->end()
                ->end()
                ->tab('tab.rz_news_post_sets_has_posts_settings')
                    ->with('tab.group.rz_news_post_sets_has_posts_settings', array('class' => 'col-md-8'))->end()
                ->end();

            $formMapper
                ->tab('tab.rz_news_post_sets_has_posts')
                    ->with('tab.group.rz_news_post_sets_has_posts', array('class' => 'col-md-12'))
                        ->add('post', 'sonata_type_model_list', array('btn_delete' => false, 'required'=>true), array('link_parameters' => $this->getPostSettings()))
                        ->add('position')
                        ->add('enabled', null, array('required' => false))
                        ->add('publicationDateStart', 'sonata_type_datetime_picker', array('dp_side_by_side' => true))
                    ->end()
                ->end();

            $instance = $this->getSubject();
            if ($instance && $instance->getId()) {
                $this->provider->load($instance);
                $this->provider->buildEditForm($formMapper);
            } else {
                $this->provider->buildCreateForm($formMapper);
            }
        } else {
            $formMapper->add('post', 'sonata_type_model_list', array('btn_delete' => false), array('link_parameters' => $this->getPostSettings()));
            $formMapper
                ->add('enabled', null, array('required' => false))
                ->add('publicationDateStart', 'sonata_type_datetime_picker', array('dp_side_by_side' => true))
                ->add('position', 'hidden');

        }
    }

    /**
     * @param  \Sonata\AdminBundle\Datagrid\ListMapper $listMapper
     * @return void
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('post', null, array('associated_property' => 'title'))
            ->add('position', null, array('footable'=>array('attr'=>array('data-breakpoints'=>array('xs', 'sm'))), 'editable' => true))
            ->add('post.publicationDateStart', null, array('footable'=>array('attr'=>array('data-breakpoints'=>array('xs', 'sm')))))
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureDatagridFilters(DatagridMapper $filter)
    {
        $filter
            ->add('post.site', null, array('show_filter' => false))
            ->add('post.title')
            ->add('post.publicationDateStart', 'doctrine_orm_datetime_range', array('field_type' => 'sonata_type_datetime_range_picker'));
    }

    /**
     * {@inheritdoc}
     */
    public function prePersist($object)
    {
        parent::prePersist($object);
        if($this->hasProvider()) {
            $this->provider->prePersist($object);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function preUpdate($object)
    {
        parent::preUpdate($object);
        if($this->hasProvider()) {
            $this->provider->preUpdate($object);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function postUpdate($object)
    {
        parent::postUpdate($object);
        if($this->hasProvider()) {
            $this->getProvider()->postUpdate($object);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function postPersist($object)
    {
        parent::postPersist($object);
        if($this->hasProvider()) {
            $this->getProvider()->postPersist($object);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function validate(ErrorElement $errorElement, $object)
    {
        parent::validate($errorElement, $object);
        if($this->hasProvider()) {
            $this->getProvider()->validate($errorElement, $object);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getPersistentParameters()
    {
        $parameters = parent::getPersistentParameters();

        if ($this->hasRequest() && $this->getRequest()->get('collection')) {
            $parameters['collection'] = $this->getRequest()->get('collection');
        }

        if(is_array($parameters) && isset($parameters['collection'])) {
            $parameters = array_merge($parameters, array('hide_collection' => $this->hasRequest() ? (int) $this->getRequest()->get('hide_collection', 0) : 0));
        } else {
            $collectionSlug = $this->getSlugify()->slugify($this->getDefaultCollection());
            $parameters = array(
                'collection'      => $collectionSlug,
                'hide_collection' => $this->hasRequest() ? (int) $this->getRequest()->get('hide_collection', 0) : 0);
        }

        if ($this->getSubject() && $this->getSubject()->getPostSets()) {
            $parameters['collection'] = $this->getSubject()->getPostSets()->getCollection() ? $this->getSubject()->getPostSets()->getCollection()->getSlug() : $collectionSlug;
            return $parameters;
        }

        return $parameters;
    }

    public function fetchProviderKey() {
        $collectionSlug = $this->getPersistentParameter('collection');

        $collection = null;
        if($collectionSlug) {
            $collection = $this->collectionManager->findOneBy(array('slug'=>$collectionSlug));
        } else {
            $collection = $this->collectionManager->findOneBy(array('slug'=>$this->getDefaultCollection()));
        }
        if($collection) {
            return $collection;
        } else {
            return;
        }
    }

    public function getPoolProvider(PoolInterface $pool) {
        $currentCollection = $this->fetchProviderKey();

        if ($currentCollection && $pool->hasCollection($currentCollection->getSlug())) {
            $providerName = $pool->getProviderNameByCollection($currentCollection->getSlug());
            $provider = $pool->getProvider($providerName);
            $params = $pool->getSettingsByCollection($currentCollection->getSlug());
            $provider = $pool->getProvider($providerName);
            ###############################
            # Load provoder levelsettings
            ###############################
            $provider->setRawSettings($params);
            return $provider;
        }

        return;
    }

    public function getProviderName(PoolInterface $pool, $providerKey = null) {
        if(!$providerKey) {
            $providerKey = $this->fetchProviderKey();
        }

        if ($providerKey && $pool->hasCollection($providerKey->getSlug())) {
            return $pool->getProviderNameByCollection($providerKey->getSlug());
        }

        return null;
    }

    public function getPostSettings() {

        $settings = parent::getPostSettings();

        if(!$this->hasProvider()) {
            return $settings;
        }

        $providerSettings = [];
        $providerSettings = $this->getProvider()->getPostSettings();

        if($providerSettings) {
            $settings = array_merge($settings, $providerSettings);
        }

        return $settings;
    }

    /**
     * {@inheritdoc}
     */
    public function getNewInstance()
    {
        $instance = parent::getNewInstance();
        $this->provider = $this->getPoolProvider($this->getPool());
        return $instance;
    }
}
