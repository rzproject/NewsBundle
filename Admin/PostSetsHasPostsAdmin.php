<?php


namespace Rz\NewsBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Route\RouteCollection;

class PostSetsHasPostsAdmin extends Admin
{
    protected $parentAssociationMapping = 'postSets';
    protected $pool;
    protected $defaultContext;
    protected $defaultCollection;
    protected $collectionManager;


    /**
     * @param \Sonata\AdminBundle\Form\FormMapper $formMapper
     *
     * @return void
     */
    protected function configureFormFields(FormMapper $formMapper)
    {

        $provider = $this->getPoolProvider();
        $instance = $this->getSubject();

        if($provider) {
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
                        ->add('post', 'sonata_type_model_list', array('btn_delete' => false), array())
                        ->add('enabled', null, array('required' => false))
                        ->add('publicationDateStart', 'sonata_type_datetime_picker', array('dp_side_by_side' => true))
                        ->add('position', 'hidden')
                    ->end()
                ->end();

            if ($instance && $instance->getId()) {
                $provider->load($instance);
                $provider->buildEditForm($formMapper);
            } else {
                $provider->buildCreateForm($formMapper);
            }

        } else {

            $formMapper->add('post', 'sonata_type_model_list', array('btn_delete' => false), array());
            $formMapper
                ->add('enabled', null, array('required' => false))
                ->add('publicationDateStart', 'sonata_type_datetime_picker', array('dp_side_by_side' => true))
                ->add('position', 'hidden')
            ;

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
            return $this->pool->getProvider($providerName);
        }

        return;
    }
}
