<?php

namespace Rz\NewsBundle\Admin;

use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Knp\Menu\ItemInterface as MenuItemInterface;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\CoreBundle\Model\ManagerInterface;
use Sonata\CoreBundle\Validator\ErrorElement;
use Rz\CoreBundle\Provider\PoolInterface;
use Rz\CoreBundle\Admin\AdminProviderInterface;


class PostAdmin extends AbstractPostAdmin
{
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

    public function setSubject($subject)
    {
        parent::setSubject($subject);
        $this->provider = $this->getPoolProvider($this->getPool());
    }

    /**
     * {@inheritdoc}
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        if($this->hasProvider()) {
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
                ->end();
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
                ->end();
        }

        if($this->getPostHasMediaEnabled()) {
            $formMapper
                ->tab('tab.rz_news_media')
                    ->with('rz_news_media', array('class' => 'col-md-12'))->end()
                ->end();
        }


        if($this->getRelatedArticleEnabled()) {
            $formMapper
                ->tab('tab.rz_news_related_articles')
                    ->with('rz_news_related_articles', array('class' => 'col-md-12'))->end()
                ->end();
        }


        if($this->getSuggestedArticleEnabled()) {
            $formMapper
                ->tab('tab.rz_news_suggested_articles')
                    ->with('rz_news_suggested_articles', array('class' => 'col-md-12'))->end()
                ->end();
        }

        ##############################
        # POST
        ##############################

        $formMapper
            ->tab('tab.rz_news')
                ->with('group_post', array(
                        'class' => 'col-md-8',
                    ))
                    ->add('title')
                    ->add('image', 'sonata_type_model_list', array('required' => false), array('link_parameters' => $this->getMediaSettings()))
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
                        'listener'             => true))
                ->end()
            ->end();

        ##############################
        # TAGS
        ##############################

        $formMapper
            ->tab('tab.rz_news_tags')
                ->with('group_classification', array('class' => 'col-md-8'))
                    ->add('tags', 'sonata_type_model', array(
                        'property' => 'name',
                        'multiple' => 'true',
                        'required' => false,
                        'expanded' => true,
                        'query'    => $this->getTagManager()->geTagQueryForDatagrid(array($this->getTagsSettingsForQuery()))),
                        array('link_parameters' => $this->getTagsSettings()))
                ->end()
            ->end();

        ##############################
        # CATEGORY
        ##############################

        $formMapper
            ->tab('tab.rz_news_category')
                ->with('rz_news_category', array('class' => 'col-md-12'))
                    ->add('postHasCategory', 'sonata_type_collection', array(
                        'cascade_validation' => true,
                        'required' => false),
                         array(
                                'edit'              => 'inline',
                                'inline'            => 'table',
                                'sortable'          => 'position',
                                'link_parameters'   => $this->getPostHasCagegorySettings(),
                                'admin_code'        => 'rz.news.admin.post_has_category',
                            ))
                ->end()
            ->end();

        ##############################
        # MEDIA
        ##############################

        if($this->getPostHasMediaEnabled()) {
            $formMapper
                ->tab('tab.rz_news_media')
                    ->with('rz_news_media', array('class' => 'col-md-12'))
                        ->add('postHasMedia', 'sonata_type_collection', array(
                            'cascade_validation' => true,
                            'required'           => false),
                            array(
                                    'edit'            => 'inline',
                                    'inline'          => 'standard',
                                    'sortable'        => 'position',
                                    'link_parameters' => $this->getPostHasMediaSettings(),
                                    'admin_code'      => 'rz.news.admin.post_has_media'))
                    ->end()
                ->end();
        }

        ##############################
        # RELATED ARTICLE
        ##############################

        if($this->getRelatedArticleEnabled()) {
            $formMapper
                ->tab('tab.rz_news_related_articles')
                    ->with('rz_news_related_articles', array('class' => 'col-md-12'))
                        ->add('relatedArticles', 'sonata_type_collection', array(
                            'cascade_validation' => true,
                            'required' => false),
                            array(
                                'edit'              => 'inline',
                                'inline'            => 'table',
                                'sortable'          => 'position',
                                'link_parameters'   => array('context' => $this->getDefaultContext()),
                                'admin_code'        => 'rz.news.admin.related_articles'))
                    ->end()
                ->end();
        }

        ##############################
        # SUGGESTED ARTICLE
        ##############################

        if($this->getSuggestedArticleEnabled()) {
            $formMapper
                ->tab('tab.rz_news_suggested_articles')
                    ->with('rz_news_suggested_articles', array('class' => 'col-md-12'))
                        ->add('suggestedArticles', 'sonata_type_collection', array(
                            'cascade_validation' => true,
                            'required' => false),
                            array(
                                    'edit'              => 'inline',
                                    'inline'            => 'table',
                                    'sortable'          => 'position',
                                    'link_parameters'   => $this->getSuggetedArticleSettings(),
                                    'admin_code'        => 'rz.news.admin.suggested_articles'))
                    ->end()
                ->end();
        }

        $instance = $this->getSubject();

        #ADD page template if news does not use controller
        if ($this->isGranted('ROLE_ALLOWED_TO_SWITCH')) {
            $formMapper->tab('tab.rz_news')->with('group_status');
            if ($instance && $instance->getId()) {
                $formMapper->add('author', 'sonata_type_model_list');
            }
            $formMapper->end()->end();
        }

        if($this->hasProvider()) {
            if ($instance && $instance->getId()) {
                $this->getProvider()->load($instance);
                $this->getProvider()->buildEditForm($formMapper, $instance);
            } else {
                $this->getProvider()->buildCreateForm($formMapper, $instance);
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
    public function getPostHasMediaEnabled()
    {
        if($this->hasProvider() && $this->getProvider()->getPostHasMediaEnabled() !== null) {
            return  $this->getProvider()->getPostHasMediaEnabled();
        }
        return $this->postHasMediaEnabled;
    }

    /**
     * @return mixed
     */
    public function getSuggestedArticleEnabled()
    {
        if($this->hasProvider() && $this->getProvider()->getSuggestedArticleEnabled() !== null) {
            return  $this->getProvider()->getSuggestedArticleEnabled();
        }
        return $this->suggestedArticleEnabled;
    }

    /**
     * @return mixed
     */
    public function getRelatedArticleEnabled()
    {
        if($this->hasProvider() && $this->getProvider()->getRelatedArticleEnabled() !== null) {
            return  $this->getProvider()->getRelatedArticleEnabled();
        }
        return $this->relatedArticleEnabled;
    }

    /**
     * {@inheritdoc}
     */
    public function getPersistentParameters()
    {
        $parameters = array(
            'collection'      => $this->getDefaultCollection(),
            'hide_collection' => $this->hasRequest() ? (int) $this->getRequest()->get('hide_collection', 0) : 0);

        if ($this->getSubject()) {
            $parameters['collection'] = $this->getSubject()->getCollection() ? $this->getSubject()->getCollection()->getSlug() : $this->getDefaultCollection();
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

        if(!$providerName = $this->getProviderName($pool, $currentCollection)) {
            return null;
        }

        $params = $pool->getSettingsByCollection($currentCollection->getSlug());

        $provider = $pool->getProvider($providerName);
        ###############################
        # Load provoder levelsettings
        ###############################
        $provider->setRawSettings($params);
        return $provider;
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
    public function prePersist($object)
    {
        parent::prePersist($object);

        if($this->getPostHasMediaEnabled()) {
            $object->setPostHasMedia($object->getPostHasMedia());
        }

        if($this->getRelatedArticleEnabled()) {
            $object->setRelatedArticles($object->getRelatedArticles());
        }

        if($this->getSuggestedArticleEnabled()) {
            $object->setSuggestedArticles($object->getSuggestedArticles());
        }

        $object->setPostHasCategory($object->getPostHasCategory());
        $object->setAuthor($this->getCurrentUser());
        if($this->hasProvider()) {
            $object->setProvider($this->getProviderName($this->getPool()));
            $this->getProvider()->prePersist($object);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function preUpdate($object)
    {
        parent::preUpdate($object);

        if($this->getPostHasMediaEnabled()) {
            $object->setPostHasMedia($object->getPostHasMedia());
        }

        if($this->getRelatedArticleEnabled()) {
            $object->setRelatedArticles($object->getRelatedArticles());
        }

        if($this->getSuggestedArticleEnabled()) {
            $object->setSuggestedArticles($object->getSuggestedArticles());
        }

        $object->setPostHasCategory($object->getPostHasCategory());

        if($this->hasProvider()) {
            $object->setProvider($this->getProviderName($this->getPool()));
            $this->getProvider()->preUpdate($object);
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

    public function getPostHasMediaSettings() {

        $settings = parent::getPostHasMediaSettings();

        if(!$this->hasProvider()) {
            return $settings;
        }

        $providerSettings = [];
        $providerSettings = $this->getProvider()->getPostHasMediaSettings();
        if($providerSettings) {
            $settings = array_merge($settings, $providerSettings);
        }
        return $settings;
    }

    public function getSuggetedArticleSettings() {

        $settings = parent::getSuggetedArticleSettings();

        if(!$this->hasProvider()) {
            return $settings;
        }

        $providerSettings = [];
        $providerSettings = $this->getProvider()->getSuggetedArticleSettings();
        if($providerSettings) {
            $settings = array_merge($settings, $providerSettings);
        }
        return $settings;
    }

    public function getPostHasCagegorySettings() {

        $settings = parent::getPostHasCagegorySettings();

        if(!$this->hasProvider()) {
            return $settings;
        }

        $providerSettings = [];
        $providerSettings = $this->getProvider()->getPostHasCagegorySettings();
        if($providerSettings) {
            $settings = array_merge($settings, $providerSettings);
        }
        return $settings;
    }

    public function getTagsSettingsForQuery() {
        $settings = $this->getTagsSettings();
        return isset($settings['context']) ? $settings['context'] : $this->getDefaultContext();
    }

    public function getTagsSettings() {

        $settings = parent::getTagsSettings();

        if(!$this->hasProvider()) {
            return $settings;
        }

        $providerSettings = [];
        $providerSettings = $this->getProvider()->getTagsSettings();
        if($providerSettings) {
            $settings = array_merge($settings, $providerSettings);
        }
        return $settings;
    }

    public function getMediaSettings() {

        $settings = parent::getMediaSettings();

        if(!$this->hasProvider()) {
            return $settings;
        }

        $providerSettings = [];
        $providerSettings = $this->getProvider()->getMediaSettings();

        if($providerSettings) {
            $settings = array_merge($settings, $providerSettings);
        }
        return $settings;
    }
}
