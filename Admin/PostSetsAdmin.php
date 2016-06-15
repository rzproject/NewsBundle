<?php

namespace Rz\NewsBundle\Admin;

use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Rz\CoreBundle\Provider\PoolInterface;
use Rz\CoreBundle\Admin\AdminProviderInterface;
use Sonata\CoreBundle\Validator\ErrorElement;

class PostSetsAdmin extends AbstractPostSetsAdmin implements AdminProviderInterface
{
    protected $formOptions = array('cascade_validation'=>true);

    /**
     * {@inheritdoc}
     */
    public function setSubject($subject)
    {
        parent::setSubject($subject);
        $this->provider = $this->getPoolProvider($this->getPool());
        $this->childeProvider = $this->getPoolProvider($this->getChildPool());
    }

    /**
     * {@inheritdoc}
     */
    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('name')
            ->add('description')
            ->add('updatedAt')
            ->add('createdAt');
    }

    /**
     * {@inheritdoc}
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('name')
            ->add('description', null, array('footable' => array('attr' => array('data-breakpoints' => array('xs', 'sm')))));
    }

    /**
     * {@inheritdoc}
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('name')
            ->add('description')
            ->add('collection', 'doctrine_orm_model_autocomplete', array('show_filter' => false), null, array(
                'property' => 'name',
                'callback' => function ($admin, $property, $value) {
                    $datagrid = $admin->getDatagrid();
                    $queryBuilder = $datagrid->getQuery();
                    $queryBuilder->andWhere(sprintf('%s.context = :context', $queryBuilder->getRootAlias()));
                    $queryBuilder->setParameter('context', $this->getDefaultContext());
                }
            ));
    }

    /**
     * {@inheritdoc}
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->tab('tab.rz_news_post_sets')
                ->with('tab.group.rz_news_post', array('class' => 'col-md-12'))->end()
            ->end()
            ->tab('tab.rz_news_post_sets_has_posts')
                ->with('tab.group.rz_news_post_sets_has_posts', array('class' => 'col-md-12'))->end()
            ->end();


        if($this->hasChildProvider()) {
            $postSetsHasPostsFieldOptions = array(
                'edit'            => 'inline',
                'inline'          => 'standard',
                'sortable'        => 'position',
                'admin_code'      => 'rz.news.admin.post_sets_has_posts',
                'link_parameters' => $this->getPostSetsHasPostsSettings(),
            );
            $postSetsHasPostsTabSettings = array('class' => 'col-md-8');
        } else {
            $postSetsHasPostsFieldOptions = array(
                'edit'            => 'inline',
                'inline'          => 'table',
                'sortable'        => 'position',
                'admin_code'      => 'rz.news.admin.post_sets_has_posts',
                'link_parameters' => $this->getPostSetsHasPostsSettings(),
            );
            $postSetsHasPostsTabSettings = array('class' => 'col-md-12');
        }

        if($this->hasProvider()){
            $postSetsTabSettings = array('class' => 'col-md-6');
        } else {
            $postSetsTabSettings = array('class' => 'col-md-12');
        }



        $formMapper
            ->tab('tab.rz_news_post_sets')
                ->with('tab.group.rz_news_post', $postSetsTabSettings)
                    ->add('name')
                    ->add('description', null, array('attr'=>array('rows'=>14)))
                ->end()
            ->end()
            ->tab('tab.rz_news_post_sets_has_posts')
                ->with('tab.group.rz_news_post_sets_has_posts', $postSetsHasPostsTabSettings)
                    ->add('postSetsHasPosts',
                          'sonata_type_collection',
                          array('cascade_validation' => true,
                                'required' => false),
                          $postSetsHasPostsFieldOptions)
                ->end()
            ->end();

        if($this->hasProvider()) {
            $instance = $this->getSubject();
            if ($instance && $instance->getId()) {
                $this->provider->load($instance);
                $this->provider->buildEditForm($formMapper);
            } else {
                $this->provider->buildCreateForm($formMapper);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function prePersist($object)
    {
        parent::prePersist($object);
        $object->setPostSetsHasPosts($object->getPostSetsHasPosts());
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
        $object->setPostSetsHasPosts($object->getPostSetsHasPosts());
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

        if ($pool->hasCollection($currentCollection->getSlug())) {
            $providerName = $pool->getProviderNameByCollection($currentCollection->getSlug());

            if(!$providerName) {
                return null;
            }
            $provider = $pool->getProvider($providerName);
            $params = $pool->getSettingsByCollection($currentCollection->getSlug());
            $provider = $pool->getProvider($providerName);
            ###############################
            # Load provoder levelsettings
            ###############################
            $provider->setRawSettings($params);
            return $provider;
        }
        return null;
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

        if ($this->getSubject()) {
            $parameters['collection'] = $this->getSubject()->getCollection() ? $this->getSubject()->getCollection()->getSlug() : $collectionSlug;
            return $parameters;
        }

        return $parameters;
    }

    /**
     * {@inheritdoc}
     */
    public function getNewInstance()
    {
        $instance = parent::getNewInstance();

        $context = $this->contextManager->findOneBy(array('id'=>$this->getSlugify()->slugify($this->getDefaultContext())));

        if(!$context && !$context instanceof \Sonata\ClassificationBundle\Model\ContextInterface) {
            $context = $this->contextManager->generateDefaultContext($this->getDefaultContext());
        }

        $collectionSlug = $this->getPersistentParameter('collection') ?: $this->getSlugify()->slugify($this->getDefaultCollection());
        $collections = $this->collectionManager->findBy(array('context'=>$context));
        $collection = $this->collectionManager->findOneBy(array('slug'=>$collectionSlug, 'context'=>$context));
        if (!$collections && !$collection && !$collection instanceof \Sonata\ClassificationBundle\Model\CollectionInterface) {
            $collection = $this->collectionManager->generateDefaultCollection($context, $this->getDefaultCollection());
        }

        if(!$collection) {
            $collection = current($collections);
        }

        $instance->setCollection($collection);

        return $instance;
    }

    public function getPostSetsHasPostsSettings() {
        $settings = [];
        $settings['collection'] = $this->getPersistentParameter('collection');
        return $settings;
    }
}