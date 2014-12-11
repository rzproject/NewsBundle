<?php

/*
 * This file is part of the RzNewsBundle package.
 *
 * (c) mell m. zamora <mell@rzproject.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rz\NewsBundle\Admin;

use Sonata\NewsBundle\Admin\PostAdmin as BaseAdmin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\ClassificationBundle\Model\ContextManagerInterface;
use Sonata\ClassificationBundle\Model\CollectionManagerInterface;
use Rz\NewsBundle\Provider\Pool;


class PostAdmin extends BaseAdmin
{
    const POST_DEFAULT_CONTEXT = 'news';

    const POST_DEFAULT_COLLECTION = 'blog';

    protected $contextManager;

    protected $collectionManager;

    protected $formOptions = array('validation_groups'=>array('admin'), 'cascade_validation'=>true);

    protected $pool;


    /**
     * {@inheritdoc}
     */
    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('author')
            ->add('collection')
            ->add('enabled')
            ->add('title')
            ->add('abstract','text')
            ->add('content', 'text', array('safe' => true))
            ->add('tags')
            ->add('postHasCategory')
            ->add('postHasMedia')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('title', null, array('footable'=>array('attr'=>array('data_toggle'=>true))))
            ->add('enabled', null, array('editable' => true))
            ->add('publicationDateStart', null, array('footable'=>array('attr'=>array('data_hide'=>'phone,tablet'))))
            ->add('_action', 'actions', array(
                'actions' => array(
                    'Show' => array('template' => 'SonataAdminBundle:CRUD:list__action_show.html.twig'),
                    'Edit' => array('template' => 'SonataAdminBundle:CRUD:list__action_edit.html.twig'),
                    'Delete' => array('template' => 'SonataAdminBundle:CRUD:list__action_delete.html.twig')),
                'footable'=>array('attr'=>array('data_hide'=>'phone,tablet')),
            ))
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureFormFields(FormMapper $formMapper)
    {

        $post = $this->getSubject();
        $em = $this->modelManager->getEntityManager('Application\Sonata\ClassificationBundle\Entity\Tag');

        $context = $this->contextManager->find('news');

        $query = $em->createQueryBuilder('t')
            ->select('t')
            ->from('ApplicationSonataClassificationBundle:Tag', 't')
            ->where('t.context = :context')
            ->andWhere('t.enabled = :enabled')
            ->orderBy('t.name', 'ASC')
            ->setParameters(array('context'=>$context, 'enabled'=>true));

        $formMapper
            ->with('Post')
                ->add('enabled', null, array('required' => false))
                ->add('author', 'sonata_type_model_list', array('validation_groups' => 'Default'))
                ->add('title', null)
                ->add('abstract', null, array('attr' => array('rows' => 5)))
                ->add('image', 'sonata_type_model_list',array('required' => false, 'attr'=>array('class'=>'span8')), array('link_parameters' => array('context' => 'news', 'hide_context' => true)))
                ->add('content', 'sonata_formatter_type', array(
                        'event_dispatcher' => $formMapper->getFormBuilder()->getEventDispatcher(),
                        'error_bubbling' => false,
                        'format_field'   => 'contentFormatter',
                        'source_field'   => 'rawContent',
                        'ckeditor_context' => 'news',
                        'source_field_options'      => array(
                            'error_bubbling'=>false,
                            'attr' => array('rows' => 20)
                        ),
                        'target_field'   => 'content',
                        'listener'       => true,
                ))
            ->end();


       $provider = $this->getPoolProvider();

        if ($post->getId()) {
            $provider->buildEditForm($formMapper);
        } else {
            $provider->buildCreateForm($formMapper);
        }

        $formMapper
            ->with('Category', array('class' => 'col-md-4'))
                ->add('postHasCategory', 'sonata_type_collection', array(
                        'cascade_validation' => true,
                        'error_bubbling' => false,
                    ), array(
                        'edit' => 'inline',
                        'inline' => 'table',
                        'sortable'  => 'position',
                        'link_parameters' => array('context' => 'news', 'hide_context' => true, 'mode' => 'list'),
                        'admin_code' => 'rz_news.admin.post_has_category',
                        'error_bubbling' => false,
                    )
                )
            ->end()

            ->with('Media', array('class' => 'col-md-4'))
                ->add('postHasMedia', 'sonata_type_collection', array(
                        'cascade_validation' => true,
                        'error_bubbling' => false,
                    ), array(
                        'edit' => 'inline',
                        'sortable'  => 'position',
                        'link_parameters' => array('context' => 'news', 'hide_context' => true, 'mode' => 'list'),
                        'admin_code' => 'rz_news.admin.post_has_media',
                        'error_bubbling' => false,
                    )
                )
            ->end()
            ->with('Tags')
                ->add('tags', 'sonata_type_model', array(
                    'required' => false,
                    'multiple' => true,
                    'select2'=>true,
                    'query' => $query
                    ),
                    array('link_parameters' => array('context' => 'news', 'hide_context' => true)))
            ->end()
            ->with('Status')
                ->add('enabled', null, array('required' => false))
                ->add('commentsCloseAt')
                ->add('commentsEnabled', null, array('required' => false))
                ->add('commentsEnabled', null, array('required' => false))
                ->add('commentsDefaultStatus', 'sonata_news_comment_status', array('expanded' => true))
            ->end()
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('title')
            ->add('enabled')
            ->add('collection')
            ->add('author', null, array('field_options' => array('selectpicker_enabled'=>true)))
            ->add('tags', null, array('field_options' => array('expanded' => false, 'multiple' => true, 'selectpicker_enabled'=>true)))
            ->add('with_open_comments', 'doctrine_orm_callback', array(
                                          'callback' => function ($queryBuilder, $alias, $field, $data) {
                                              if (!is_array($data) || !$data['value']) {
                                                  return;
                                              }

                                              $commentClass = $this->commentManager->getClass();

                                              $queryBuilder->leftJoin(sprintf('%s.comments', $alias), 'c');
                                              $queryBuilder->andWhere('c.status = :status');
                                              $queryBuilder->setParameter('status', $commentClass::STATUS_MODERATE);
                                          },
                                          'field_type' => 'checkbox'
                                      ))
        ;
    }

    public function setContextManager(ContextManagerInterface $contextManager) {
        $this->contextManager = $contextManager;
    }

    public function setCollectionManager(CollectionManagerInterface $collectionManager) {
        $this->collectionManager = $collectionManager;
    }

    public function setPostManager(CollectionManagerInterface $collectionManager) {
        $this->collectionManager = $collectionManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getPersistentParameters()
    {
        $parameters = array(
            'collectionId'      => $this->getDefaultCollection(),
            'hide_collection' => (int)$this->getRequest()->get('hide_context', 0)
        );

        if ($this->getSubject()) {
            $parameters['collectionId'] = $this->getSubject()->getCollection() ? $this->getSubject()->getCollection()->getId() : '';

            return $parameters;
        }

        if ($this->hasRequest()) {
            $parameters['collectionId'] = $this->getRequest()->get('collectionId');

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

        if ($collectionId = $this->getPersistentParameter('collectionId') ?: $this->getDefaultCollection()) {
            $collection =  $this->collectionManager->find($collectionId);

            if (!$collection) {

                $context = $this->contextManager->find(self::POST_DEFAULT_CONTEXT);

                if (!$context) {
                    $context = $this->contextManager->create();
                    $context->setEnabled(true);
                    $context->setId(self::POST_DEFAULT_CONTEXT);
                    $context->setName(ucwords(self::POST_DEFAULT_CONTEXT));

                    $this->contextManager->save($context);
                }

                $collection = $this->collectionManager->create();
                $collection->setEnabled(true);
                $collection->setName(self::POST_DEFAULT_COLLECTION);
                $collection->setContext($context);
                $this->collectionManager->save($collection);
            }

            $instance->setCollection($collection);
        }

        return $instance;
    }

    public function setPool(Pool $pool) {
        $this->pool = $pool;
    }

    protected function fetchCurrentCollection() {

        $collectionId = $this->getPersistentParameter('collectionId');
        $collection = null;
        if($collectionId) {
            $collection = $this->collectionManager->find($collectionId);
        } else {
            $collection = $this->collectionManager->findOneBy(array('slug'=>self::POST_DEFAULT_COLLECTION));
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
        } else {
            $providerName = $this->pool->getProviderNameByCollection($this->pool->getDefaultCollection());
            return $this->pool->getProvider($providerName);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function prePersist($object)
    {
        parent::prePersist($object);
        $parameters = $this->getPersistentParameters();
        if(isset($parameters['collectionId'])) {
            $collection = $this->collectionManager->find($parameters['collectionId']);
            $object->setCollection($collection);
        }
        $object->setPostHasCategory($object->getPostHasCategory());
        $object->setPostHasMedia($object->getPostHasMedia());
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

    public function getDefaultCollection() {

        return $this->collectionManager->findOneBy(array('slug'=>$this->pool->getDefaultCollection())) ?: null;
    }
}
