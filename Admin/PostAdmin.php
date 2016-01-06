<?php

namespace Rz\NewsBundle\Admin;

use Sonata\NewsBundle\Admin\PostAdmin as Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Knp\Menu\ItemInterface as MenuItemInterface;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\CoreBundle\Model\ManagerInterface;

class PostAdmin extends Admin
{
    protected $collectionManager;

    protected $contextManager;

    protected $pool;

    const NEWS_DEFAULT_COLLECTION = 'article';

    protected $datagridValues = array(
        '_page' => 1,
        '_sort_order' => 'DESC',
        '_sort_by' => 'publicationDateStart',
    );

    /**
     * {@inheritdoc}
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
         // define group zoning
        $formMapper
            ->tab('News')
                ->with('group_post', array('class' => 'col-md-8'))->end()
                ->with('group_status', array('class' => 'col-md-4'))->end()
                ->with('group_classification', array('class' => 'col-md-4'))->end()
            ->end()
            ->tab('Settings')
                ->with('rz_news_settings', array('class' => 'col-md-12'))->end()
            ->end()
            ->tab('Category')
                ->with('rz_news_category', array('class' => 'col-md-12'))->end()
            ->end()
            ->tab('Media')
                ->with('rz_news_media', array('class' => 'col-md-12'))->end()
            ->end()
            ->tab('tab.rz_news_related_articles')
                ->with('rz_news_related_articles', array('class' => 'col-md-12'))->end()
            ->end()
            ->tab('tab.rz_news_suggested_articles')
                ->with('rz_news_suggested_articles', array('class' => 'col-md-12'))->end()
            ->end()
        ;


        $formMapper
            ->tab('News')
                ->with('group_post', array(
                        'class' => 'col-md-8',
                    ))
                    ->add('author', 'sonata_type_model_list')
                    ->add('title')
                    ->add('abstract', null, array('attr' => array('rows' => 5)))
                    ->add('content', 'sonata_formatter_type', array(
                        'event_dispatcher'          => $formMapper->getFormBuilder()->getEventDispatcher(),
                        'format_field'              => 'contentFormatter',
                        'source_field'              => 'rawContent',
                        'source_field_options'      => array(
                            'horizontal_input_wrapper_class' => $this->getConfigurationPool()->getOption('form_type') == 'horizontal' ? 'col-lg-12' : '',
                            'attr'                           => array('class' => $this->getConfigurationPool()->getOption('form_type') == 'horizontal' ? 'span10 col-sm-10 col-md-10' : '', 'rows' => 20),
                        ),
                        'ckeditor_context'     => 'news',
                        'target_field'         => 'content',
                        'listener'             => true,
                    ))
                ->end()
                ->with('group_status', array(
                        'class' => 'col-md-4',
                    ))
                    ->add('enabled', null, array('required' => false))
                    ->add('image', 'sonata_type_model_list', array('required' => false), array(
                        'link_parameters' => array(
                            'context'      => 'news',
                            'hide_context' => true,
                        ),
                    ))
                    ->add('publicationDateStart', 'sonata_type_datetime_picker', array('dp_side_by_side' => true))
                ->end()

                ->with('group_classification', array(
                    'class' => 'col-md-4',
                    ))
                    ->add('tags', 'sonata_type_model_autocomplete', array(
                        'property' => 'name',
                        'multiple' => 'true',
                        'required' => false,
                        'callback' => function ($admin, $property, $value) {
                            $datagrid = $admin->getDatagrid();
                            $queryBuilder = $datagrid->getQuery();
                            $queryBuilder
                                ->andWhere($queryBuilder->getRootAlias() . '.context=:context')
                                ->setParameter('context', 'news')
                            ;
                            $datagrid->setValue($property, null, $value);
                        },
                    ))
                ->end()
            ->end()
        ;

        $formMapper
            ->tab('Category')
                ->with('rz_news_category', array('class' => 'col-md-8'))
                    ->add('postHasCategory', 'sonata_type_collection', array(
                        'cascade_validation' => true,
                        'required' => false,
                    ), array(
                            'edit'              => 'inline',
                            'inline'            => 'standard',
                            'sortable'          => 'position',
                            'link_parameters'   => array('context' => 'news'),
                            'admin_code'        => 'rz.news.admin.post_has_category',
                        )
                    )
                ->end()
            ->end()
        ;

        $formMapper
            ->tab('Media')
                ->with('rz_news_media', array('class' => 'col-md-8'))
                    ->add('postHasMedia', 'sonata_type_collection', array(
                        'cascade_validation' => true,
                        'required' => false,
                    ), array(
                            'edit'              => 'inline',
                            'inline'            => 'standard',
                            'sortable'          => 'position',
                            'link_parameters'   => array('context' => 'news'),
                            'admin_code'        => 'rz.news.admin.post_has_media',
                        )
                    )
                ->end()
            ->end()
        ;

        $formMapper
            ->tab('tab.rz_news_related_articles')
                ->with('rz_news_related_articles', array('class' => 'col-md-8'))
                    ->add('relatedArticles', 'sonata_type_collection', array(
                        'cascade_validation' => true,
                        'required' => false,
                    ), array(
                            'edit'              => 'inline',
                            'inline'            => 'table',
                            'sortable'          => 'position',
                            'link_parameters'   => array('context' => 'news'),
                            'admin_code'        => 'rz.news.admin.related_articles',
                        )
                    )
                ->end()
            ->end()
        ;

        $formMapper
            ->tab('tab.rz_news_suggested_articles')
                ->with('rz_news_suggested_articles', array('class' => 'col-md-8'))
                    ->add('suggestedArticles', 'sonata_type_collection', array(
                        'cascade_validation' => true,
                        'required' => false,
                    ), array(
                            'edit'              => 'inline',
                            'inline'            => 'table',
                            'sortable'          => 'position',
                            'link_parameters'   => array('context' => 'news'),
                            'admin_code'        => 'rz.news.admin.suggested_articles',
                        )
                    )
                ->end()
            ->end()
        ;

        $provider = $this->getPoolProvider();
        $instance = $this->getSubject();

        if ($instance && $instance->getId()) {
            $provider->load($instance);
            $provider->buildEditForm($formMapper);
        } else {
            $provider->buildCreateForm($formMapper);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('custom', 'string', array('template' => 'RzNewsBundle:PostAdmin:list_post_custom.html.twig', 'label' => 'Post'))
            ->add('enabled', null, array('editable' => true, 'footable'=>array('attr'=>array('data-breakpoints'=>array('all')))))
            ->add('publicationDateStart', null, array('footable'=>array('attr'=>array('data-breakpoints'=>array('all')))))
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureSideMenu(MenuItemInterface $menu, $action, AdminInterface $childAdmin = null)
    {
        if (!$childAdmin && !in_array($action, array('edit'))) {
            return;
        }

        $admin = $this->isChild() ? $this->getParent() : $this;

        $id = $admin->getRequest()->get('id');

        $menu->addChild(
            $this->trans('sidemenu.link_edit_post'),
            array('uri' => $admin->generateUrl('edit', array('id' => $id)))
        );

        if ($this->hasSubject() && $this->getSubject()->getId() !== null) {
            $menu->addChild(
                $this->trans('sidemenu.link_view_post'),
                array('uri' => $admin->getRouteGenerator()->generate('sonata_news_view', array('permalink' => $this->permalinkGenerator->generate($this->getSubject()))))
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('collection', 'doctrine_orm_model_autocomplete', array(), null, array(
                'property' => 'name',
                'callback' => function ($admin, $property, $value) {
                    $datagrid = $admin->getDatagrid();
                    $queryBuilder = $datagrid->getQuery();
                    $queryBuilder->andWhere(sprintf('%s.context = :context', $queryBuilder->getRootAlias()));
                    $queryBuilder->setParameter('context', 'news');
                }

            ))
            ->add('title')
            ->add('enabled')
            ->add('tags', null, array('field_options' => array('expanded' => true, 'multiple' => true)))
            ->add('author')
            ->add('publicationDateStart', 'doctrine_orm_datetime_range', array('field_type' => 'sonata_type_datetime_range_picker'))
        ;
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
     * {@inheritdoc}
     */
    public function getPersistentParameters()
    {
        $parameters = array(
            'collection'      => 'article',
            'hide_collection' => $this->hasRequest() ? (int) $this->getRequest()->get('hide_collection', 0) : 0,
        );

        if ($this->getSubject()) {
            $parameters['collection'] = $this->getSubject()->getCollection() ? $this->getSubject()->getCollection()->getSlug() : '';

            return $parameters;
        }

        if ($this->hasRequest()) {
            $parameters['collection'] = $this->getRequest()->get('collection');

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

        if ($collectionSlug = $this->getPersistentParameter('collection')?: 'article') {
            $collection = $this->collectionManager->findOneBy(array('slug'=>$collectionSlug));

            if (!$collection) {
                //find 'news' context
                $context = $this->contextManager->find('news');
                if(!$context) {
                    $context = $this->contextManager->create();
                    $context->setEnabled(true);
                    $context->setId($context);
                    $context->setName($context);
                    $this->contextManager->save($context);
                }
                //create collection
                $collection = $this->collectionManager->create();
                $collection->setContext($context);
                $name = ucwords(str_replace('-', ' ',$collectionSlug));
                $collection->setName($name);
                $collection->setDescription($name);
                $this->collectionManager->save($collection);
            }

            $instance->setCollection($collection);
        }

        return $instance;
    }

    protected function fetchCurrentCollection() {

        $collectionSlug = $this->getPersistentParameter('collection');
        $collection = null;
        if($collectionSlug) {
            $collection = $this->collectionManager->findOneBy(array('slug'=>$collectionSlug));
        } else {
            $collection = $this->collectionManager->findOneBy(array('slug'=>self::NEWS_DEFAULT_COLLECTION));
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
    public function prePersist($object)
    {
        parent::prePersist($object);
        $object->setPostHasCategory($object->getPostHasCategory());
        $object->setPostHasMedia($object->getPostHasMedia());
        $object->setRelatedArticles($object->getRelatedArticles());
        $object->setSuggestedArticles($object->getSuggestedArticles());
        $this->getPoolProvider()->prePersist($object);
    }

    /**
     * {@inheritdoc}
     */
    public function preUpdate($object)
    {
        parent::preUpdate($object);
        $object->setPostHasCategory($object->getPostHasCategory());
        $object->setPostHasMedia($object->getPostHasMedia());
        $object->setRelatedArticles($object->getRelatedArticles());
        $object->setSuggestedArticles($object->getSuggestedArticles());
        $this->getPoolProvider()->preUpdate($object);
    }


    /**
     * {@inheritdoc}
     */
    public function postUpdate($object)
    {
        parent::postUpdate($object);
        $this->getPoolProvider()->postUpdate($object);
    }

    /**
     * {@inheritdoc}
     */
    public function postPersist($object)
    {
        parent::postPersist($object);
        $this->getPoolProvider()->postPersist($object);
    }

}
