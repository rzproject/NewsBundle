<?php


namespace Rz\NewsBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Show\ShowMapper;

class PostSetsAdmin extends Admin
{

    protected $pool;

    protected $defaultContext;

    protected $defaultCollection;

    protected $collectionManager;

    protected $contextManager;

    protected $slugify;

    const POST_SETS_DEFAULT_COLLECTION = 'default';

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
            ->tab('tab.rz_news_post_sets_settings')
                ->with('tab.group.rz_news_post_sets_settings', array('class' => 'col-md-12'))->end()
            ->end()
            ->tab('tab.rz_news_post_sets_has_posts')
                ->with('tab.group.rz_news_post_sets_has_posts', array('class' => 'col-md-12'))->end()
            ->end();


        $formMapper
            ->tab('tab.rz_news_post_sets')
                ->with('tab.group.rz_news_post', array('class' => 'col-md-12'))
                    ->add('name')
                    ->add('description')
                ->end()
            ->end()
            ->tab('tab.rz_news_post_sets_has_posts')
                ->with('tab.group.rz_news_post_sets_has_posts', array('class' => 'col-md-12'))
                    ->add('postSetsHasPosts', 'sonata_type_collection', array(
                        'cascade_validation' => true,
                        'required'           => false,
                        'label'              => false
                    ), array(
                            'edit'            => 'inline',
                            'inline'          => 'table',
                            'sortable'        => 'position',
                            'admin_code'      => 'rz.news.admin.post_sets_has_posts',
                        )
                    )
                ->end()
            ->end();


        $provider = $this->getPoolProvider();
        dump($provider);
        die();
        $instance = $this->getSubject();

        if ($instance && $instance->getId()) {
            $provider->load($instance);
            $provider->buildEditForm($formMapper);
        } else {
            $provider->buildCreateForm($formMapper);
        }
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
     * {@inheritdoc}
     */
    public function prePersist($object)
    {
        parent::prePersist($object);
        $object->setPostSetsHasPosts($object->getPostSetsHasPosts());
    }

    /**
     * {@inheritdoc}
     */
    public function preUpdate($object)
    {
        parent::preUpdate($object);
        $object->setPostSetsHasPosts($object->getPostSetsHasPosts());
    }

    protected function fetchCurrentCollection() {

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

    protected function getPoolProvider() {
        $currentCollection = $this->fetchCurrentCollection();
        if ($this->pool->hasCollection($currentCollection->getSlug())) {
            $providerName = $this->pool->getProviderNameByCollection($currentCollection->getSlug());
        } else {
            $providerName = $this->pool->getProviderNameByCollection($this->pool->getDefaultCollection());
        }

        return $this->pool->getProvider($providerName);
    }

    /**
     * {@inheritdoc}
     */
    public function getPersistentParameters()
    {
        $parameters = parent::getPersistentParameters();
        $collectionSlug = $this->getSlugify()->slugify($this->getDefaultCollection());
        if(is_array($parameters)) {
            $parameters = array_merge($parameters, array(
                'collection'      => $collectionSlug,
                'hide_collection' => $this->hasRequest() ? (int) $this->getRequest()->get('hide_collection', 0) : 0));
        } else {
            $parameters = array(
                'collection'      => $collectionSlug,
                'hide_collection' => $this->hasRequest() ? (int) $this->getRequest()->get('hide_collection', 0) : 0);
        }

        if ($this->getSubject()) {
            $parameters['collection'] = $this->getSubject()->getCollection() ? $this->getSubject()->getCollection()->getSlug() : $collectionSlug;
            return $parameters;
        }

        if ($this->hasRequest() && $this->getRequest()->get('collection')) {
            $parameters['collection'] = $this->getRequest()->get('collection');
        }

        return $parameters;
    }

    /**
     * {@inheritdoc}
     */
    public function getNewInstance()
    {
        $instance = parent::getNewInstance();

        $galleryContext = $this->contextManager->findOneBy(array('id'=>$this->getSlugify()->slugify($this->getDefaultContext())));

        if(!$galleryContext && !$galleryContext instanceof \Sonata\ClassificationBundle\Model\ContextInterface) {
            $galleryContext = $this->getContextManager->generateDefaultContext($this->getDefaultContext());
        }

        $collectionSlug = $this->getPersistentParameter('collection') ?: $this->getSlugify()->slugify($this->getDefaultCollection());
        $collections = $this->collectionManager->findBy(array('context'=>$galleryContext));
        $collection = $this->collectionManager->findOneBy(array('slug'=>$collectionSlug, 'context'=>$galleryContext));

        if (!$collections && !$collection && !$collection instanceof \Sonata\ClassificationBundle\Model\CollectionInterface) {
            $collection = $this->collectionManager->generateDefaultColection($galleryContext, $this->getDefaultCollection());
        }

        $instance->setCollection($collection);
    }
}