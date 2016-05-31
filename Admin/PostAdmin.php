<?php

namespace Rz\NewsBundle\Admin;

use Sonata\NewsBundle\Admin\PostAdmin as Admin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Knp\Menu\ItemInterface as MenuItemInterface;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\CoreBundle\Model\ManagerInterface;
use Sonata\CoreBundle\Validator\ErrorElement;

class PostAdmin extends Admin
{
    protected $collectionManager;

    protected $contextManager;

    protected $siteManager;

    protected $tagManager;

    protected $pool;

    protected $transformer;

    protected $defaultContext;

    protected $defaultCollection;

    protected $slugify;

    protected $seoProvider;

    protected $isControllerEnabled;

    protected $pageTemplates = [];

    protected $securityTokenStorage;

    protected $datagridValues = array(
        '_page'       => 1,
        '_per_page'   => 12,
        '_sort_order' => 'DESC',
        '_sort_by'    => 'publicationDateStart',
    );

    /**
     * @param string $code
     * @param string $class
     * @param string $baseControllerName
     */
    public function __construct($code, $class, $baseControllerName)
    {
        $this->maxPerPage = 12;
        $this->perPageOptions = array(8,12,16,32);
        parent::__construct($code, $class, $baseControllerName);
    }

    /**
     * {@inheritdoc}
     */
    protected function configureFormFields(FormMapper $formMapper)
    {

        //POST PROVIDER
        $provider = $this->getPoolProvider();

        if($provider) {
            $formMapper
                ->tab('tab.rz_news')
                    ->with('group_post', array('class' => 'col-md-8'))->end()
                    ->with('group_status', array('class' => 'col-md-4'))->end()
                    ->with('group_content', array('class' => 'col-md-12'))->end()
                ->end()
                ->tab('tab.rz_news_settings')
                    ->with('rz_news_settings', array('class' => 'col-md-12'))->end()
                ->end()
                ->tab('tab.rz_news_tags')
                    ->with('group_classification', array('class' => 'col-md-12'))->end()
                ->end()
                ->tab('tab.rz_news_category')
                    ->with('rz_news_category', array('class' => 'col-md-12'))->end()
                ->end()
                ->tab('tab.rz_news_media')
                    ->with('rz_news_media', array('class' => 'col-md-12'))->end()
                ->end()
                ->tab('tab.rz_news_related_articles')
                    ->with('rz_news_related_articles', array('class' => 'col-md-12'))->end()
                ->end()
                ->tab('tab.rz_news_suggested_articles')
                    ->with('rz_news_suggested_articles', array('class' => 'col-md-12'))->end()
                ->end()
            ;
        } else {
            $formMapper
                ->tab('tab.rz_news')
                    ->with('group_post', array('class' => 'col-md-8'))->end()
                    ->with('group_status', array('class' => 'col-md-4'))->end()
                    ->with('group_content', array('class' => 'col-md-12'))->end()
                ->end()
                ->tab('tab.rz_news_tags')
                    ->with('group_classification', array('class' => 'col-md-12'))->end()
                ->end()
                ->tab('tab.rz_news_category')
                    ->with('rz_news_category', array('class' => 'col-md-12'))->end()
                ->end()
                ->tab('tab.rz_news_media')
                    ->with('rz_news_media', array('class' => 'col-md-12'))->end()
                ->end()
                ->tab('tab.rz_news_related_articles')
                    ->with('rz_news_related_articles', array('class' => 'col-md-12'))->end()
                ->end()
                ->tab('tab.rz_news_suggested_articles')
                    ->with('rz_news_suggested_articles', array('class' => 'col-md-12'))->end()
                ->end()
            ;
        }

        if (interface_exists('Sonata\PageBundle\Model\PageInterface')) {

            $formMapper->tab('tab.rz_news_suggested_articles')
                            ->with('rz_news_suggested_articles', array('class' => 'col-md-12'))->end()
                       ->end();


            $formMapper->tab('tab.rz_news_seo_settings')
                            ->with('rz_news_seo_settings', array('class' => 'col-md-12'))->end()
                       ->end();
        }


        $formMapper
            ->tab('tab.rz_news')
                ->with('group_post', array(
                        'class' => 'col-md-8',
                    ))
                    ->add('title')
                    ->add('image', 'sonata_type_model_list', array('required' => false), array(
                        'link_parameters' => array(
                            'context'      => $this->getDefaultContext(),
                            'hide_context' => true,
                        ),
                    ))
                    ->add('abstract', null, array('attr' => array('rows' => 5)))
                ->end()
                ->with('group_status', array('class' => 'col-md-4',))
                    ->add('enabled', null, array('required' => false))
                    ->add('publicationDateStart', 'sonata_type_datetime_picker', array('dp_side_by_side' => true))
                    ->add('publicationDateEnd',   'sonata_type_datetime_picker', array('dp_side_by_side' => true))
                ->end()

                ->with('group_content', array('class' => 'col-md-12',))
                    ->add('content', 'sonata_formatter_type', array(
                        'event_dispatcher'          => $formMapper->getFormBuilder()->getEventDispatcher(),
                        'format_field'              => 'contentFormatter',
                        'source_field'              => 'rawContent',
                        'source_field_options'      => array(
                            'horizontal_input_wrapper_class' => $this->getConfigurationPool()->getOption('form_type') == 'horizontal' ? 'col-lg-12' : '',
                            'attr'                           => array('class' => $this->getConfigurationPool()->getOption('form_type') == 'horizontal' ? 'span10 col-sm-10 col-md-10' : '', 'rows' => 20),
                        ),
                        'ckeditor_context'     => $this->getDefaultContext(),
                        'target_field'         => 'content',
                        'listener'             => true,
                    ))
                ->end()
            ->end()
        ;

        $formMapper
            ->tab('tab.rz_news_tags')
            ->with('group_classification', array('class' => 'col-md-8'))
                    ->add('tags', 'sonata_type_model', array(
                        'property' => 'name',
                        'multiple' => 'true',
                        'required' => false,
                        'expanded' => true,
                        'query'    => $this->getTagManager()->geTagQueryForDatagrid(array($this->getDefaultContext()))
                    ),
                        array(
                            'link_parameters' => array(
                                'context'      => $this->getDefaultContext(),
                                'hide_context' => true,
                            ))
                    )
                ->end()
            ->end()
        ;


        $formMapper
            ->tab('tab.rz_news_category')
                ->with('rz_news_category', array('class' => 'col-md-12'))
                    ->add('postHasCategory', 'sonata_type_collection', array(
                        'cascade_validation' => true,
                        'required' => false,
                    ), array(
                            'edit'              => 'inline',
                            'inline'            => 'table',
                            'sortable'          => 'position',
                            'link_parameters'   => array('context' => $this->getDefaultContext()),
                            'admin_code'        => 'rz.news.admin.post_has_category',
                        )
                    )
                ->end()
            ->end()
        ;

        $formMapper
            ->tab('tab.rz_news_media')
                ->with('rz_news_media', array('class' => 'col-md-12'))
                    ->add('postHasMedia', 'sonata_type_collection', array(
                        'cascade_validation' => true,
                        'required' => false,
                    ), array(
                            'edit'              => 'inline',
                            'inline'            => 'standard',
                            'sortable'          => 'position',
                            'link_parameters'   => array('context' => $this->getDefaultContext()),
                            'admin_code'        => 'rz.news.admin.post_has_media',
                        )
                    )
                ->end()
            ->end()
        ;

        $formMapper
            ->tab('tab.rz_news_related_articles')
                ->with('rz_news_related_articles', array('class' => 'col-md-12'))
                    ->add('relatedArticles', 'sonata_type_collection', array(
                        'cascade_validation' => true,
                        'required' => false,
                    ), array(
                            'edit'              => 'inline',
                            'inline'            => 'table',
                            'sortable'          => 'position',
                            'link_parameters'   => array('context' => $this->getDefaultContext()),
                            'admin_code'        => 'rz.news.admin.related_articles',
                        )
                    )
                ->end()
            ->end()
        ;

        $formMapper
            ->tab('tab.rz_news_suggested_articles')
                ->with('rz_news_suggested_articles', array('class' => 'col-md-12'))
                    ->add('suggestedArticles', 'sonata_type_collection', array(
                        'cascade_validation' => true,
                        'required' => false,
                    ), array(
                            'edit'              => 'inline',
                            'inline'            => 'table',
                            'sortable'          => 'position',
                            'link_parameters'   => array('context' => $this->getDefaultContext()),
                            'admin_code'        => 'rz.news.admin.suggested_articles',
                        )
                    )
                ->end()
            ->end()
        ;

        $instance = $this->getSubject();

        #ADD page template if news does not use controller
        if ($this->isGranted('ROLE_ALLOWED_TO_SWITCH')) {
            $formMapper->tab('tab.rz_news')->with('group_status');
            if ($instance && $instance->getId()) {
                $formMapper->add('author', 'sonata_type_model_list');
            }
            $formMapper->end()->end();
        }


        if($provider) {
            if ($instance && $instance->getId()) {
                $provider->load($instance);
                $provider->buildEditForm($formMapper, $instance);
            } else {
                $provider->buildCreateForm($formMapper, $instance);
            }

            //ADD page template if news does not use controller
            $formMapper->tab('tab.rz_news_settings')->with('rz_news_settings');
            if (interface_exists('Sonata\PageBundle\Model\BlockInteractorInterface') &&
                $formMapper->has('settings') &&
                !$this->getIsControllerEnabled()
            ) {

                $settingsField = $formMapper->get('settings');

                if ($instance && $instance->getId() && $instance->getSetting('pageTemplateCode')) {
                    $settingsField->add('pageTemplateCode',
                        'text',
                        array('help_block' => $this->getTranslator()->trans('help.provider_page_template_code', array(), 'SonataNewsBundle'),
                              'required'   => true,
                              'attr'       => array('readonly' => 'readonly')
                        ));
                } else {
                    $settingsField->add('pageTemplateCode',
                        'choice',
                        array('choices'    => $this->getPageTemplates(),
                              'help_block' => $this->getTranslator()->trans('help.provider_page_template_code_new', array(), 'SonataNewsBundle'),
                              'required'   => true
                        ));
                }
            }

            $formMapper->end()->end();
        }

        //SEO PROVIDER
        $seoProvider = $this->getSeoProvider();
        if($seoProvider && interface_exists('Sonata\PageBundle\Model\PageInterface')) {
            if ($instance && $instance->getId()) {
                $seoProvider->load($instance);
                $seoProvider->buildEditForm($formMapper, $instance);
            } else {
                $seoProvider->buildCreateForm($formMapper, $instance);
            }
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
            ->add('publicationDateEnd', null, array('footable'=>array('attr'=>array('data-breakpoints'=>array('all')))))
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
    }

    /**
     * {@inheritdoc}
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('site', null, array('show_filter' => false))
            ->add('collection', 'doctrine_orm_model_autocomplete', array('show_filter' => false), null, array(
                'property' => 'name',
                'callback' => function ($admin, $property, $value) {
                    $datagrid = $admin->getDatagrid();
                    $queryBuilder = $datagrid->getQuery();
                    $queryBuilder->andWhere(sprintf('%s.context = :context', $queryBuilder->getRootAlias()));
                    $queryBuilder->setParameter('context', $this->getDefaultContext());
                }

            ))
            ->add('title')
            ->add('enabled')
            ->add('tags', null, array('field_options' => array('expanded' => true, 'multiple' => true)))
            ->add('author')
            ->add('publicationDateStart', 'doctrine_orm_datetime_range', array('field_type' => 'sonata_type_datetime_range_picker'))
            ->add('publicationDateEnd',   'doctrine_orm_datetime_range', array('field_type' => 'sonata_type_datetime_range_picker'))
        ;
    }

    /**
     * @return mixed
     */
    public function getTransformer()
    {
        return $this->transformer;
    }

    /**
     * @param mixed $transformer
     */
    public function setTransformer($transformer)
    {
        $this->transformer = $transformer;
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
    public function getSiteManager()
    {
        return $this->siteManager;
    }

    /**
     * @param mixed $siteManager
     */
    public function setSiteManager(ManagerInterface $siteManager)
    {
        $this->siteManager = $siteManager;
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
     * {@inheritdoc}
     */
    public function getPersistentParameters()
    {
        $site = $this->getSite();

        $parameters = array(
            'collection'      => $this->getDefaultCollection(),
            'site'            => $site ? $site : '',
            'hide_collection' => $this->hasRequest() ? (int) $this->getRequest()->get('hide_collection', 0) : 0);

        if ($this->getSubject()) {
            $parameters['collection'] = $this->getSubject()->getCollection() ? $this->getSubject()->getCollection()->getSlug() : $this->getDefaultCollection();
            $parameters['site']       = $this->getSubject()->getSite() ? $this->getSubject()->getSite()->getId() : '';
            return $parameters;
        }

        if ($this->hasRequest()) {
            $parameters['collection'] = $this->getRequest()->get('collection');
            $parameters['site'] = $this->getRequest()->get('site');
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

        if ($site = $this->getSite()) {
            $instance->setSite($site);
        }

        $postContext = $this->contextManager->findOneBy(array('id'=>$this->getSlugify()->slugify($this->getDefaultContext())));

        if(!$postContext && !$postContext instanceof \Sonata\ClassificationBundle\Model\ContextInterface) {
            $postContext = $this->getContextManager->generateDefaultContext($this->getDefaultContext());
        }

        $collectionSlug = $this->getPersistentParameter('collection') ?: $this->getSlugify()->slugify($this->getDefaultCollection());
        $collections = $this->collectionManager->findBy(array('context'=>$postContext));
        $collection = $this->collectionManager->findOneBy(array('slug'=>$collectionSlug, 'context'=>$postContext));

        if (!$collections && !$collection && !$collection instanceof \Sonata\ClassificationBundle\Model\CollectionInterface) {
            $collection = $this->collectionManager->generateDefaultCollection($postContext, $this->getDefaultCollection());
        }

        $instance->setCollection($collection);

        return $instance;
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


        if ($currentCollection && $this->pool->hasCollection($currentCollection->getSlug())) {
            $providerName = $this->pool->getProviderNameByCollection($currentCollection->getSlug());
        } else {
            $providerName = $this->pool->getProviderNameByCollection($this->pool->getDefaultCollection());
        }

        if(!$providerName) {
            return null;
        }

        $defaultTemplate = $this->pool->getDefaultTemplateByCollection($currentCollection->getSlug());
        $provider = $this->pool->getProvider($providerName);
        //set default template
        $provider->setDefaultTemplate($defaultTemplate);
        return $provider;
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
        $object->setAuthor($this->getCurrentUser());
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
        if (interface_exists('Sonata\PageBundle\Model\PageInterface')) {
            $object->setPostHasPage($object->getPostHasPage());
            $this->getTransformer()->update($object);
        }


    }

    /**
     * @return SiteInterface|bool
     *
     * @throws \RuntimeException
     */
    public function getSite()
    {
        if (!$this->hasRequest()) {
            return false;
        }

        $siteId = null;

        if ($this->getRequest()->getMethod() == 'POST') {
            $values = $this->getRequest()->get($this->getUniqid());
            $siteId = isset($values['site']) ? $values['site'] : null;
        }

        $siteId = (null !== $siteId) ? $siteId : $this->getRequest()->get('site');

        if ($siteId) {
            $site = $this->siteManager->findOneBy(array('id' => $siteId));

            if (!$site) {
                throw new \RuntimeException('Unable to find the site with id='.$this->getRequest()->get('site'));
            }

            return $site;
        } else {
            return $this->siteManager->findOneBy(array('host'=>'localhost'));
        }
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

        if (interface_exists('Sonata\PageBundle\Model\PageInterface')) {
            $object->setPostHasPage($object->getPostHasPage());
            $this->getTransformer()->create($object);
        }
    }

    /**
     * @return mixed
     */
    public function getSeoProvider()
    {
        return $this->seoProvider;
    }

    /**
     * @param mixed $seoProvider
     */
    public function setSeoProvider($seoProvider)
    {
        $this->seoProvider = $seoProvider;
    }

    /**
     * @return mixed
     */
    public function getIsControllerEnabled()
    {
        return $this->isControllerEnabled;
    }

    /**
     * @param mixed $isControllerEnabled
     */
    public function setIsControllerEnabled($isControllerEnabled)
    {
        $this->isControllerEnabled = $isControllerEnabled;
    }

    /**
     * @return array
     */
    public function getPageTemplates()
    {
        return $this->pageTemplates;
    }

    /**
     * @param array $pageTemplates
     */
    public function setPageTemplates($pageTemplates)
    {
        $this->pageTemplates = $pageTemplates;
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
     * {@inheritdoc}
     */
    public function validate(ErrorElement $errorElement, $object)
    {
    }
}
